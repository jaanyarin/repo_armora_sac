<?php

namespace App\Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'venta_id' => $this->venta_id,
            'producto_id' => $this->producto_id,
            'producto' => $this->whenLoaded('producto', fn() => [
                'id' => $this->producto->id,
                'codigo' => $this->producto->codigo,
                'nombre' => $this->producto->nombre,
                'unidad_medida' => $this->producto->unidadMedida?->simbolo,
            ]),
            'unidad_medida_id' => $this->unidad_medida_id,
            'numero_linea' => $this->numero_linea,
            'cantidad' => (float) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento_linea' => (float) $this->descuento_linea,
            'subtotal' => (float) $this->subtotal,
            'igv' => (float) $this->igv,
            'total' => (float) $this->total,
            'observaciones' => $this->observaciones,
        ];
    }
}
