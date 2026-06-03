<?php

namespace App\Modules\Customers\Http\Resources;

use App\Modules\Customers\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'nombre_completo' => $this->nombre_completo,
            'nombre_comercial' => $this->nombre_comercial,
            'direccion' => $this->direccion,
            'ubigeo_id' => $this->ubigeo_id,
            'ubigeo' => $this->whenLoaded('ubigeo'),
            'email' => $this->email,
            'telefono' => $this->telefono,
            'tipo_cliente_id' => $this->tipo_cliente_id,
            'tipo_cliente' => $this->whenLoaded('tipoCliente'),
            'segmento_id' => $this->segmento_id,
            'segmento' => $this->whenLoaded('segmento'),
            'lista_precio_id' => $this->lista_precio_id,
            'limite_credito' => $this->limite_credito,
            'activo' => $this->activo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
