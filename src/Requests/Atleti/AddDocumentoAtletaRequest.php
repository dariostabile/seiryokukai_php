<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

final class AddDocumentoAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
            'idtipo_documento' => 'required|int|min:1',
            'descrizione_documento' => 'nullable|string|max:45',
            'data_documento' => 'nullable|date',
            'data_scadenza' => 'nullable|date',
            'url_documento' => 'nullable|string|max:255',
        ];
    }

    protected function messages(): array
    {
        return [
            'idatleta.required' => 'Seleziona un atleta valido',
            'idtipo_documento.required' => 'Il tipo documento è obbligatorio',
            'idtipo_documento.min' => 'Il tipo documento selezionato non è valido',
        ];
    }
}
