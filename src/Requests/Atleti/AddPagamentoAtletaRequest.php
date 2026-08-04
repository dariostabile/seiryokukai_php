<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

use App\Requests\FormRequest;
use App\Requests\ValidationException;

final class AddPagamentoAtletaRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'idatleta' => 'required|int|min:1',
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
            'idcorso.required' => 'Seleziona un corso valido',
            'data_pagamento.required' => 'La data pagamento è obbligatoria',
            'quota_pagamento.required' => 'L\'importo pagamento è obbligatorio',
        ];
    }

    protected function afterValidation(): void
    {
        $paymentDate = (string) ($this->data['data_pagamento'] ?? '');
        $expiryDate = (string) ($this->data['data_scadenza'] ?? '');

        if ($paymentDate !== '' && $expiryDate !== '' && $expiryDate < $paymentDate) {
            throw new ValidationException([
                'data_scadenza' => 'La data scadenza non puo essere precedente alla data pagamento',
            ]);
        }
    }
}
