<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

class AddAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'titolo' => 'nullable|string|max:45',
            'nome' => 'required|string|min:2|max:255',
            'cognome' => 'required|string|min:2|max:255',
            'codice_fiscale' => 'nullable|string|min:11|max:16',
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
            'status.in' => 'Lo stato selezionato non è valido',
            'sesso.in' => 'Il sesso deve essere M o F',
        ];
    }
}
