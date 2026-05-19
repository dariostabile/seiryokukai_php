<?php

declare(strict_types=1);

namespace App\Requests;

class UpdateSiteRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'id' => 'required|int|min:1',
            'name' => 'required|string|min:1|max:255',
            'code' => 'string|max:50',
            'active' => 'nullable|int|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'id.required' => 'ID della sede obbligatorio',
            'name.required' => 'Il nome della sede è obbligatorio',
            'name.min' => 'Il nome della sede deve contenere almeno 1 carattere',
            'name.max' => 'Il nome della sede non deve superare 255 caratteri',
            'code.max' => 'Il codice sede non deve superare 50 caratteri',
            'active.min' => 'Lo stato sede non è valido',
            'active.max' => 'Lo stato sede non è valido',
        ];
    }
}
