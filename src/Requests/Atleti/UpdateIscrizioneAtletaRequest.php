<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

final class UpdateIscrizioneAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
            'idcorso_attuale' => 'required|int|min:1',
            'course_ids' => 'required|array',
            'data_inizio_iscrizione' => 'required|date',
            'data_fine_iscrizione' => 'nullable|date',
            'totale_iscrizione' => 'nullable|float|min:0',
            'stato_iscrizione' => 'required|string|in:A,S,C',
            'note_iscrizione' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'idatleta.required' => 'Atleta non valido',
            'idcorso_attuale.required' => 'Corso attuale non valido',
            'course_ids.required' => 'Seleziona almeno un corso',
            'data_inizio_iscrizione.required' => 'La data inizio iscrizione è obbligatoria',
            'stato_iscrizione.required' => 'Lo stato iscrizione è obbligatorio',
            'stato_iscrizione.in' => 'Lo stato iscrizione selezionato non è valido',
        ];
    }
}
