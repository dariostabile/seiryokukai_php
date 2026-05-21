<?php

declare(strict_types=1);

namespace App\Requests\TipiDocumenti;

use App\Requests\FormRequest;

class AddTipoDocumentoRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'type' => 'required|string|min:1|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'type.required' => 'Il tipo di documento è obbligatorio',
            'type.min' => 'Il tipo di documento deve contenere almeno 1 carattere',
            'type.max' => 'Il tipo di documento non deve superare 255 caratteri',
        ];
    }
}
