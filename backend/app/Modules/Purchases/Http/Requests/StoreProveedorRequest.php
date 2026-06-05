<?php

namespace App\Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-proveedores');
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:20', 'unique:purchases_proveedores,codigo'],
            'tipo_documento_id' => ['nullable', 'integer', 'exists:dim_documento_tipo,id'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'nombre_completo' => ['required', 'string', 'max:200'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'contacto_nombre' => ['nullable', 'string', 'max:200'],
            'activo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'nombre_completo.required' => 'El nombre o razón social es obligatorio.',
        ];
    }
}
