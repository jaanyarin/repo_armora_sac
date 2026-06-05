<?php

namespace App\Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'compra_id' => $this->compra_id,
            'producto_id' => $this->producto_id,
            'unidad_medida_id' => $this->unidad_medida_id,
            'numero_linea' => $this->numero_linea,
            'cantidad' => (float) $this->cantidad,
            'precio_unitario' => (float) $this->precio_unitario,
            'descuento_linea' => (float) $this->descuento_linea,
            'subtotal' => (float) $this->subtotal,
            'igv' => (float) $this->igv,
            'total' => (float) $this->total,
            'observaciones' => $this->observaciones,
            'producto' => $this->whenLoaded('producto'),
            'unidad_medida' => $this->whenLoaded('unidadMedida'),
        ];
    }
}
