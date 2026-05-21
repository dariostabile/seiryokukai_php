<?php

declare(strict_types=1);

namespace App\Requests\Atleti;

final class UpdateAtletaRequest extends AddAtletaRequest
{
    protected function rules(): array
    {
        return array_merge([
            'id' => 'required|int|min:1',
        ], parent::rules());
    }

    protected function messages(): array
    {
        return array_merge([
            'id.required' => 'L\'ID atleta è obbligatorio',
            'id.min' => 'L\'ID atleta non è valido',
        ], parent::messages());
    }
}
