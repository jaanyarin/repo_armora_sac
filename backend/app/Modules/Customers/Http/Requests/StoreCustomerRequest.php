<?php

namespace App\Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:20', 'unique:customers,codigo'],
            'tipo_documento' => ['required', 'string', 'in:DNI,RUC,CE,PASAPORTE'],
            'numero_documento' => ['required', 'string', 'max:15', 'unique:customers,numero_documento'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['nullable', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ubigeo_id' => ['nullable', 'integer', 'exists:dim_ubigeo,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo_cliente_id' => ['nullable', 'integer', 'exists:dim_tipo_cliente,id'],
            'segmento_id' => ['nullable', 'integer', 'exists:dim_segmento_sunat,id'],
            'lista_precio_id' => ['nullable', 'integer', 'exists:dim_lista_precios,id'],
            'limite_credito' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'El tipo de documento es obligatorio.',
            'tipo_documento.in' => 'El tipo de documento debe ser DNI, RUC, CE o PASAPORTE.',
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.unique' => 'Este número de documento ya está registrado.',
            'nombre_completo.required' => 'El nombre o razón social es obligatorio.',
        ];
    }
}
