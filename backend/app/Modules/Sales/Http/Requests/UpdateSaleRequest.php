<?php

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sale = $this->route('sale');
        return $this->user()->can('update', $sale);
    }

    public function rules(): array
    {
        return [
            'documento_tipo_id' => ['nullable', 'integer', 'exists:dim_documento_tipo,id'],
            'serie' => ['nullable', 'string', 'max:10'],
            'numero' => ['nullable', 'string', 'max:20'],
            'fecha_emision' => ['required', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_emision'],
            'descuento_global' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            // A-07: SoftDeletes — filtrar registros eliminados lógicamente
            'items.*.producto_id' => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'items.*.unidad_medida_id' => ['required', 'integer', 'exists:dim_unidad_medida,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento_linea' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'La venta debe tener al menos un ítem.',
            'items.*.producto_id.required' => 'Cada ítem debe tener un producto.',
            'items.*.cantidad.min' => 'La cantidad debe ser mayor a cero.',
        ];
    }
}
