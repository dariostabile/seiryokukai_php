<?php

declare(strict_types=1);

namespace App\Requests\Discipline;

use App\Requests\FormRequest;

class AddDisciplinaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'notes' => 'string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Il nome della disciplina è obbligatorio',
            'name.min' => 'Il nome della disciplina deve contenere almeno 1 carattere',
            'name.max' => 'Il nome della disciplina non deve superare 255 caratteri',
            'notes.max' => 'Le note non devono superare 1000 caratteri',
        ];
    }
}