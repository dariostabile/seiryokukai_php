<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

final class AddIscrizioneAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
            'data_inizio_iscrizione' => 'required|date',
            'data_fine_iscrizione' => 'nullable|date',
            'totale_iscrizione' => 'nullable|float|min:0',
            'stato_iscrizione' => 'required|string|in:A,S,C',
            'course_ids' => 'required|array',
            'note_iscrizione' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'idatleta.required' => 'Seleziona un atleta valido',
            'data_inizio_iscrizione.required' => 'La data inizio iscrizione è obbligatoria',
            'stato_iscrizione.required' => 'Lo stato iscrizione è obbligatorio',
            'stato_iscrizione.in' => 'Lo stato iscrizione selezionato non è valido',
            'course_ids.required' => 'Seleziona almeno un corso',
        ];
    }
}
