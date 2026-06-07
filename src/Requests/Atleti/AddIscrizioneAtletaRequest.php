<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;
use App\Requests\ValidationException;

final class AddIscrizioneAtletaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawCourseIds = $this->data['course_ids'] ?? [];
        $courseIds = is_array($rawCourseIds) ? $rawCourseIds : [$rawCourseIds];

        $this->data['course_ids'] = array_values(array_unique(array_filter(
            array_map('intval', $courseIds),
            static fn (int $value): bool => $value > 0
        )));
    }

    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
            'data_inizio_iscrizione' => 'required|date',
            'data_fine_iscrizione' => 'nullable|date',
            'data_iscrizione_corso' => 'nullable|date',
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

    protected function afterValidation(): void
    {
        $errors = [];

        $startDate = (string) ($this->data['data_inizio_iscrizione'] ?? '');
        $endDate = (string) ($this->data['data_fine_iscrizione'] ?? '');
        if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
            $errors['data_fine_iscrizione'] = 'La data fine iscrizione non puo essere precedente alla data inizio';
        }

        $courseIds = $this->getArray('course_ids');
        if ($courseIds === []) {
            $errors['course_ids'] = 'Seleziona almeno un corso';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}
