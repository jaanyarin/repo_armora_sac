<?php

namespace App\Modules\Personal\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pid' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'pid.required' => 'Debe seleccionar un personal.',
            'pid.integer' => 'El identificador de personal debe ser un número entero.',
            'pid.exists' => 'El personal seleccionado no existe.',
        ];
    }
}
