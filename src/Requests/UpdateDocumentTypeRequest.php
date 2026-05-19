<?php

declare(strict_types=1);

namespace App\Requests;

class UpdateDocumentTypeRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'id' => 'required|int|min:1',
            'type' => 'required|string|min:1|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'id.required' => 'ID del tipo di documento obbligatorio',
            'type.required' => 'Il tipo di documento è obbligatorio',
            'type.min' => 'Il tipo di documento deve contenere almeno 1 carattere',
            'type.max' => 'Il tipo di documento non deve superare 255 caratteri',
        ];
    }
}
