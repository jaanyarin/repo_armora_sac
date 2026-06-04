<?php

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('crear-ventas');
    }

    public function rules(): array
    {
        return [
            // A-07: SoftDeletes — filtrar registros eliminados lógicamente
            'cliente_id' => ['required', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'documento_tipo_id' => ['nullable', 'integer', 'exists:dim_documento_tipo,id'],
            'serie' => ['nullable', 'string', 'max:10'],
            'numero' => ['nullable', 'string', 'max:20'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'moneda_id' => ['nullable', 'integer', 'exists:dim_moneda,id'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'estado' => ['nullable', Rule::in(['borrador', 'confirmada'])],
            'items' => ['required', 'array', 'min:1'],
            // products también usa SoftDeletes
            'items.*.producto_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.unidad_medida_id' => ['required', 'integer', 'exists:dim_unidad_medida,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento_linea' => ['nullable', 'numeric', 'min:0'],
            'items.*.observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
            'items.required' => 'La venta debe tener al menos un ítem.',
            'items.*.producto_id.required' => 'Cada ítem debe tener un producto.',
            'items.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'items.*.cantidad.required' => 'La cantidad es obligatoria en cada ítem.',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
            'items.*.precio_unitario.required' => 'El precio unitario es obligatorio.',
        ];
    }
}
