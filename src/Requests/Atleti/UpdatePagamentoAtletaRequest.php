<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;

final class UpdatePagamentoAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
            'idpagamento' => 'required|int|min:1',
            'idcorso' => 'required|int|min:1',
            'data_pagamento' => 'required|date',
            'data_scadenza' => 'nullable|date',
            'quota_pagamento' => 'required|float|min:0',
            'note_pagamento' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'idatleta.required' => 'Seleziona un atleta valido',
            'idpagamento.required' => 'Pagamento non valido',
            'idcorso.required' => 'Seleziona un corso valido',
            'data_pagamento.required' => 'La data pagamento è obbligatoria',
            'quota_pagamento.required' => 'L\'importo pagamento è obbligatorio',
        ];
    }
}
