<?php

declare(strict_types=1);

namespace App\Requests;

class ValidationException extends \Exception
{
    /** @var array<string, string> */
    private array $errors;

    /**
     * @param array<string, string> $errors
     */
    public function __construct(array $errors = [], string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->errors = $errors;
        $message = $message !== '' ? $message : 'Validation failed';
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Ottiene il primo errore.
     */
    public function firstError(): string
    {
        return reset($this->errors) ?: 'Errore di validazione';
    }
}
