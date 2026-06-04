<?php

namespace App\Modules\Sales\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'cliente_id' => $this->cliente_id,
            'cliente' => $this->whenLoaded('cliente', fn() => [
                'id' => $this->cliente->id,
                'codigo' => $this->cliente->codigo,
                'nombre_completo' => $this->cliente->nombre_completo,
                'numero_documento' => $this->cliente->numero_documento,
            ]),
            'usuario_id' => $this->usuario_id,
            'usuario' => $this->whenLoaded('usuario', fn() => [
                'id' => $this->usuario->id,
                'nombre_completo' => $this->usuario->nombre_completo,
            ]),
            'documento_tipo_id' => $this->documento_tipo_id,
            'serie' => $this->serie,
            'numero' => $this->numero,
            'fecha_emision' => $this->fecha_emision?->format('Y-m-d'),
            'fecha_vencimiento' => $this->fecha_vencimiento?->format('Y-m-d'),
            'moneda_id' => $this->moneda_id,
            'subtotal' => (float) $this->subtotal,
            'descuento_global' => (float) $this->descuento_global,
            'igv' => (float) $this->igv,
            'isc' => (float) $this->isc,
            'total' => (float) $this->total,
            'saldo_pendiente' => (float) $this->saldo_pendiente,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'origen' => $this->origen,
            'items' => $this->whenLoaded('items', fn() => SaleItemResource::collection($this->items)),
            'items_count' => $this->whenCounted('items'),
            'notas_credito' => $this->whenLoaded('notasCredito', fn() => $this->notasCredito->map(fn($nc) => [
                'id' => $nc->id,
                'codigo' => $nc->codigo,
                'motivo' => $nc->motivo,
                'total' => (float) $nc->total,
                'estado' => $nc->estado,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
