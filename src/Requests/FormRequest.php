<?php

declare(strict_types=1);

namespace App\Requests;

abstract class FormRequest
{
    /** @var array<string, mixed> */
    protected array $data = [];

    /** @var array<string, string> */
    protected array $errors = [];

    /**
     * Definisce le regole di validazione.
     * @return array<string, string|array<string>>
     */
    abstract protected function rules(): array;

    /**
     * Messaggi di errore personalizzati (opzionale).
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Trasformazioni prima della validazione (opzionale).
     */
    protected function prepareForValidation(): void
    {
    }

    /**
     * Validazioni personalizzate dopo quelle base (opzionale).
     */
    protected function afterValidation(): void
    {
    }

    /**
     * Costruisce la request dai dati POST/GET.
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->prepareForValidation();
        $this->validate();
        $this->afterValidation();
    }

    /**
     * Esegue la validazione.
     */
    protected function validate(): void
    {
        $rules = $this->rules();
        $messages = $this->messages();
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rulesArray = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            // Se il campo e nullable ed e vuoto, ignora le altre regole.
            if (in_array('nullable', $rulesArray, true) && $this->isEmpty($value)) {
                continue;
            }

            foreach ($rulesArray as $rule) {
                $error = $this->validateRule($field, $value, $rule, $messages, $rulesArray);
                if ($error !== null) {
                    $this->errors[$field] = $error;
                    break;
                }
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
    }

    /**
     * Valida una singola regola.
     */
    private function validateRule(string $field, mixed $value, string $rule, array $messages, array $allRules = []): ?string
    {
        $messageKey = "$field.$rule";
        $customMessage = $messages[$messageKey] ?? $messages[$rule] ?? null;

        if (str_contains($rule, ':')) {
            [$ruleName, $ruleParam] = explode(':', $rule, 2);
            $messageKeyBase = "$field.$ruleName";
            $customMessage = $messages[$messageKeyBase] ?? $messages[$messageKey] ?? $messages[$ruleName] ?? $messages[$rule] ?? null;
            return $this->validateRuleWithParam($field, $value, $ruleName, $ruleParam, $customMessage, $allRules);
        }

        switch ($rule) {
            case 'required':
                if ($this->isEmpty($value)) {
                    return $customMessage ?? "$field è obbligatorio";
                }
                break;

            case 'string':
                if ($value !== null && !is_string($value)) {
                    return $customMessage ?? "$field deve essere una stringa";
                }
                break;

            case 'int':
                if ($value !== null && (!is_int($value) && !is_numeric($value))) {
                    return $customMessage ?? "$field deve essere un numero intero";
                }
                break;

            case 'float':
                if ($value !== null && !is_numeric($value)) {
                    return $customMessage ?? "$field deve essere un numero";
                }
                break;

            case 'email':
                if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return $customMessage ?? "$field deve essere un email valido";
                }
                break;

            case 'date':
                if ($value !== null && !$this->isValidDate($value)) {
                    return $customMessage ?? "$field deve essere una data valida (YYYY-MM-DD)";
                }
                break;

            case 'array':
                if ($value !== null && !is_array($value)) {
                    return $customMessage ?? "$field deve essere un array";
                }
                break;

            case 'nullable':
                break;
        }

        return null;
    }

    /**
     * Valida una regola con parametro (es. min:5, max:10).
     */
    private function validateRuleWithParam(string $field, mixed $value, string $ruleName, string $param, ?string $customMessage, array $allRules = []): ?string
    {
        if ($this->isEmpty($value)) {
            return null;
        }

        $isNumericRule = in_array('int', $allRules, true) || in_array('float', $allRules, true);

        switch ($ruleName) {
            case 'min':
                $min = (int) $param;
                if ($isNumericRule && is_numeric($value) && (float) $value < $min) {
                    return $customMessage ?? "$field deve essere almeno $min";
                }
                if (!$isNumericRule && is_string($value) && strlen($value) < $min) {
                    return $customMessage ?? "$field deve contenere almeno $min caratteri";
                }
                if (!is_string($value) && is_numeric($value) && (float) $value < $min) {
                    return $customMessage ?? "$field deve essere almeno $min";
                }
                break;

            case 'max':
                $max = (int) $param;
                if ($isNumericRule && is_numeric($value) && (float) $value > $max) {
                    return $customMessage ?? "$field non deve superare $max";
                }
                if (!$isNumericRule && is_string($value) && strlen($value) > $max) {
                    return $customMessage ?? "$field deve contenere massimo $max caratteri";
                }
                if (!is_string($value) && is_numeric($value) && (float) $value > $max) {
                    return $customMessage ?? "$field non deve superare $max";
                }
                break;

            case 'in':
                $allowed = explode(',', $param);
                if (!in_array((string) $value, $allowed, true)) {
                    return $customMessage ?? "$field non è un valore valido";
                }
                break;

            case 'regex':
                if (!preg_match($param, (string) $value)) {
                    return $customMessage ?? "$field non è valido";
                }
                break;
        }

        return null;
    }

    /**
     * Verifica se un valore è vuoto.
     */
    private function isEmpty(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) === '';
        }
        return $value === null || $value === '' || (is_array($value) && count($value) === 0);
    }

    /**
     * Verifica se una data è valida (YYYY-MM-DD).
     */
    private function isValidDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) !== false;
    }

    /**
     * Restituisce il valore di un campo.
     */
    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /**
     * Restituisce il valore trim di un campo stringa.
     */
    public function getString(string $field, string $default = ''): string
    {
        $value = $this->data[$field] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /**
     * Restituisce il valore come intero.
     */
    public function getInt(string $field, int $default = 0): int
    {
        return (int) ($this->data[$field] ?? $default);
    }

    /**
     * Restituisce il valore come float.
     */
    public function getFloat(string $field, float $default = 0.0): float
    {
        return (float) ($this->data[$field] ?? $default);
    }

    /**
     * Restituisce il valore come array.
     *
     * @return array<int, int|string|mixed>
     */
    public function getArray(string $field, array $default = []): array
    {
        $value = $this->data[$field] ?? $default;
        return is_array($value) ? $value : $default;
    }

    /**
     * Restituisce il valore booleano.
     */
    public function getBool(string $field, bool $default = false): bool
    {
        $value = $this->data[$field] ?? $default;
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }
        return (bool) $value;
    }

    /**
     * Restituisce tutti i dati validati.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Restituisce gli errori.
     *
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Verifica se ci sono errori.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
