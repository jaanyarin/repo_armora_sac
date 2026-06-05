<?php

namespace App\Modules\Purchases\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-compras');
    }

    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', Rule::exists('purchases_proveedores', 'id')->whereNull('deleted_at')],
            'documento_tipo_id' => ['nullable', 'integer', 'exists:dim_documento_tipo,id'],
            'serie' => ['nullable', 'string', 'max:10'],
            'numero' => ['nullable', 'string', 'max:20'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'moneda_id' => ['nullable', 'integer', 'exists:dim_moneda,id'],
            'almacen_id' => ['nullable', 'integer', 'exists:dim_almacen,id'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.unidad_medida_id' => ['nullable', 'integer', 'exists:dim_unidad_medida,id'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.precio_unitario' => ['required', 'numeric', 'gte:0'],
            'items.*.descuento_linea' => ['nullable', 'numeric', 'gte:0'],
            'items.*.observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
            'items.required' => 'Debe incluir al menos un ítem en la compra.',
            'items.*.producto_id.required' => 'El producto es obligatorio en cada ítem.',
            'items.*.cantidad.gt' => 'La cantidad debe ser mayor a 0.',
            'items.*.precio_unitario.gte' => 'El precio unitario debe ser mayor o igual a 0.',
        ];
    }
}
