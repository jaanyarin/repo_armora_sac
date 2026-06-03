<?php

namespace App\Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['nullable', 'string', 'max:20', 'unique:products,codigo'],
            'codigo_sunat' => ['nullable', 'string', 'max:10'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'unidad_medida_id' => ['required', 'integer', 'exists:dim_unidad_medida,id'],
            'producto_clase_id' => ['nullable', 'integer', 'exists:dim_producto_clase,id'],
            'producto_subclase_id' => ['nullable', 'integer', 'exists:dim_producto_subclase,id'],
            'familia_sunat_id' => ['nullable', 'integer', 'exists:dim_familia_sunat,id'],
            'clase_sunat_id' => ['nullable', 'integer', 'exists:dim_clase_sunat,id'],
            'tipo_afeccion_igv_id' => ['nullable', 'integer', 'exists:dim_tipo_afeccion_igv,id'],
            'tipo_calculo_isc_id' => ['nullable', 'integer', 'exists:dim_tipo_calculo_isc,id'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'precio_venta_usd' => ['nullable', 'numeric', 'min:0'],
            'costo_promedio' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'unidad_medida_id.required' => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no existe.',
        ];
    }
}
