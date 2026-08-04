<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

class AddAtletaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->normalizeNullableEmail('email_1');
        $this->normalizeNullableEmail('email_2');
        $this->normalizeNullableEmail('pec');

        if (is_string($this->data['piva'] ?? null)) {
            $this->data['piva'] = preg_replace('/\D+/', '', trim((string) $this->data['piva'])) ?: null;
        }

        if (is_string($this->data['codice_univoco_fatturazione'] ?? null)) {
            $value = strtoupper(trim((string) $this->data['codice_univoco_fatturazione']));
            $this->data['codice_univoco_fatturazione'] = $value === '' ? null : $value;
        }
    }

    protected function rules(): array
    {
        return [
            'titolo' => 'nullable|string|max:45',
            'nome' => 'required|string|min:2|max:255',
            'cognome' => 'required|string|min:2|max:255',
            'codice_fiscale' => 'nullable|string|min:11|max:16',
            'piva' => 'nullable|regex:/^[0-9]{11}$/',
            'codice_univoco_fatturazione' => 'nullable|regex:/^[A-Za-z0-9]{6,7}$/',
            'data_nascita' => 'nullable|date',
            'citta_nascita' => 'nullable|string|max:255',
            'provincia_nascita' => 'nullable|string|max:45',
            'stato_nascita' => 'nullable|string|max:255',
            'indirizzo_residenza' => 'nullable|string|max:255',
            'citta_residenza' => 'nullable|string|max:255',
            'provincia_residenza' => 'nullable|string|max:45',
            'cap_residenza' => 'nullable|string|max:45',
            'stato_residenza' => 'nullable|string|max:255',
            'sesso' => 'nullable|string|in:M,F',
            'telefono_1' => 'nullable|string|max:255',
            'telefono_2' => 'nullable|string|max:255',
            'email_1' => 'nullable|email|max:255',
            'email_2' => 'nullable|email|max:255',
            'pec' => 'nullable|email|max:255',
            'status' => 'required|string|in:Attivo,Sospeso',
            'data_scadenza_account' => 'nullable|date',
            'note_atleta' => 'nullable|string',
            'altezza' => 'nullable|int|min:50|max:250',
            'peso' => 'nullable|float|min:1|max:999',
            'misura' => 'nullable|string|max:3',
            'misura_maglia' => 'nullable|string|max:3',
            'misura_pantaloni' => 'nullable|string|max:3',
        ];
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'Il nome dell\'atleta è obbligatorio',
            'nome.min' => 'Il nome deve contenere almeno 2 caratteri',
            'cognome.required' => 'Il cognome dell\'atleta è obbligatorio',
            'cognome.min' => 'Il cognome deve contenere almeno 2 caratteri',
            'data_nascita.date' => 'La data di nascita non è valida (formato richiesto: YYYY-MM-DD)',
            'piva.regex:/^[0-9]{11}$/' => 'La P.IVA deve contenere 11 cifre',
            'codice_univoco_fatturazione.regex:/^[A-Za-z0-9]{6,7}$/' => 'Il codice univoco deve essere alfanumerico (6-7 caratteri)',
            'altezza.int' => 'L\'altezza deve essere un numero intero',
            'altezza.min' => 'L\'altezza minima è 50 cm',
            'altezza.max' => 'L\'altezza massima è 250 cm',
            'peso.float' => 'Il peso deve essere un numero valido',
            'peso.min' => 'Il peso deve essere almeno 1',
            'peso.max' => 'Il peso non deve superare 999',
            'email_1.email' => 'Email 1 non valida',
            'email_2.email' => 'Email 2 non valida',
            'pec.email' => 'PEC non valida',
            'status.in' => 'Lo stato selezionato non è valido',
            'sesso.in' => 'Il sesso deve essere M o F',
        ];
    }

    private function normalizeNullableEmail(string $field): void
    {
        $value = $this->data[$field] ?? null;

        if (!is_string($value)) {
            return;
        }

        $value = trim($value);
        $this->data[$field] = $value === '' ? null : $value;
    }
}
