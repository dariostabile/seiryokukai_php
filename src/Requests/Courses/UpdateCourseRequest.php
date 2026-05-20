<?php

declare(strict_types=1);

namespace App\Requests\Courses;

use App\Requests\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    protected function rules(): array
    {
        return [
            'course_id' => 'required|int|min:1',
            'name' => 'required|string|min:1|max:255',
            'site_id' => 'required|int|min:1',
            'discipline_id' => 'required|int|min:1',
            'user_id' => 'required|int|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monthly_fee' => 'nullable|float',
            'active' => 'nullable|int|min:0|max:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'course_id.required' => 'ID del corso obbligatorio',
            'name.required' => 'Il nome del corso è obbligatorio',
            'name.max' => 'Il nome del corso non deve superare 255 caratteri',
            'site_id.required' => 'La sede è obbligatoria',
            'discipline_id.required' => 'La disciplina è obbligatoria',
            'user_id.required' => 'L\'insegnante è obbligatorio',
            'start_date.date' => 'La data di inizio non è valida (YYYY-MM-DD)',
            'end_date.date' => 'La data di fine non è valida (YYYY-MM-DD)',
            'monthly_fee.float' => 'La quota mensile deve essere un numero',
            'active.min' => 'Lo stato corso non è valido',
            'active.max' => 'Lo stato corso non è valido',
        ];
    }
}
