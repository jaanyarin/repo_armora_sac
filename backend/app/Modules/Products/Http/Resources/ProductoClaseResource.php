<?php

namespace App\Modules\Products\Http\Resources;

use App\Modules\Products\Models\ProductoClase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductoClase */
class ProductoClaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'slug' => $this->slug,
            'descripcion' => $this->descripcion,
            'licor' => $this->licor,
            'orden' => $this->orden,
            'activo' => $this->activo,
            'subclases_count' => $this->whenCounted('subclases'),
            'subclases' => ProductoSubclaseResource::collection($this->whenLoaded('subclases')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
