<?php

namespace App\Modules\Products\Http\Resources;

use App\Modules\Products\Models\ProductoSubclase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductoSubclase */
class ProductoSubclaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'clase_id' => $this->clase_id,
            'clase' => $this->whenLoaded('clase', fn() => $this->clase ? [
                'id' => $this->clase->id,
                'codigo' => $this->clase->codigo,
                'nombre' => $this->clase->nombre,
            ] : null),
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
