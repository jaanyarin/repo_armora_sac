<?php

namespace App\Modules\Purchases\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'proveedor_id' => $this->proveedor_id,
            'usuario_id' => $this->usuario_id,
            'documento_tipo_id' => $this->documento_tipo_id,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision?->toDateString(),
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'moneda_id' => $this->moneda_id,
            'almacen_id' => $this->almacen_id,
            'subtotal' => (float) $this->subtotal,
            'descuento_global' => (float) $this->descuento_global,
            'igv' => (float) $this->igv,
            'isc' => (float) $this->isc,
            'total' => (float) $this->total,
            'saldo_pendiente' => (float) $this->saldo_pendiente,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'origen' => $this->origen,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'proveedor' => new ProveedorResource($this->whenLoaded('proveedor')),
            'usuario' => $this->whenLoaded('usuario'),
            'items' => CompraItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
