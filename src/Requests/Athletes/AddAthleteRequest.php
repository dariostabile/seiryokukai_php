<?php

declare(strict_types=1);

namespace App\Requests\Athletes;

use App\Requests\FormRequest;

class AddAthleteRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Il nome dell\'atleta è obbligatorio',
            'name.min' => 'Il nome deve contenere almeno 1 carattere',
            'name.max' => 'Il nome non deve superare 255 caratteri',
        ];
    }
}
