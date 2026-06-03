<?php

namespace App\Modules\Customers\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('customer')?->id ?? $this->route('customer');
        return [
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'codigo')->ignore($id)],
            'tipo_documento' => ['sometimes', 'string', 'in:DNI,RUC,CE,PASAPORTE'],
            'numero_documento' => ['sometimes', 'string', 'max:15', Rule::unique('customers', 'numero_documento')->ignore($id)],
            'nombre_completo' => ['sometimes', 'string', 'max:255'],
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
}
