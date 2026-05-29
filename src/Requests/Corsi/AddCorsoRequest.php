<?php

declare(strict_types=1);

namespace App\Requests\Corsi;

use App\Requests\FormRequest;

class AddCorsoRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
            'sede_id' => 'required|int|min:1',
            'disciplina_id' => 'required|int|min:1',
            'user_id' => 'required|int|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monthly_fee' => 'nullable|float',
            'active' => 'nullable|int|min:0|max:1',
            'immagine_corso' => 'nullable', // validazione custom in afterValidation
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Il nome del corso è obbligatorio',
            'name.max' => 'Il nome del corso non deve superare 255 caratteri',
            'sede_id.required' => 'La sede è obbligatoria',
            'disciplina_id.required' => 'La disciplina è obbligatoria',
            'user_id.required' => 'L\'insegnante è obbligatorio',
            'start_date.date' => 'La data di inizio non è valida (YYYY-MM-DD)',
            'end_date.date' => 'La data di fine non è valida (YYYY-MM-DD)',
            'monthly_fee.float' => 'La quota mensile deve essere un numero',
            'active.min' => 'Lo stato corso non è valido',
            'active.max' => 'Lo stato corso non è valido',
            'immagine_corso.invalid_type' => 'L\'immagine del corso deve essere un file JPG o PNG',
            'immagine_corso.max_size' => 'L\'immagine del corso non deve superare 2MB',
        ];
    }

    protected function afterValidation(): void
    {
        $file = $this->data['immagine_corso'] ?? null;
        if ($file && is_array($file)) {
            $allowedTypes = ['image/jpeg', 'image/png'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            $type = $file['type'] ?? '';
            $size = $file['size'] ?? 0;
            if (!in_array($type, $allowedTypes, true)) {
                $this->errors['immagine_corso'] = $this->messages()['immagine_corso.invalid_type'] ?? 'Tipo file non valido';
            } elseif ($size > $maxSize) {
                $this->errors['immagine_corso'] = $this->messages()['immagine_corso.max_size'] ?? 'File troppo grande';
            }
            if (!empty($this->errors)) {
                throw new \\App\\Requests\\ValidationException($this->errors);
            }
        }
    }
}
