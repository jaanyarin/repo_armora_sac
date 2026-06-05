<?php

namespace App\Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('editar-proveedores');
    }

    public function rules(): array
    {
        return [
            'tipo_documento_id' => ['sometimes', 'integer', 'exists:dim_documento_tipo,id'],
            'numero_documento' => ['sometimes', 'string', 'max:20'],
            'nombre_completo' => ['sometimes', 'string', 'max:200'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'contacto_nombre' => ['nullable', 'string', 'max:200'],
            'activo' => ['nullable', 'boolean'],
        ];
    }
}
