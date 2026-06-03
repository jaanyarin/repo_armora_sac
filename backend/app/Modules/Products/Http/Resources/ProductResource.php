<?php

namespace App\Modules\Products\Http\Resources;

use App\Modules\Products\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'codigo_sunat' => $this->codigo_sunat,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'unidad_medida_id' => $this->unidad_medida_id,
            'unidad_medida' => $this->whenLoaded('unidadMedida'),
            'producto_clase_id' => $this->producto_clase_id,
            'producto_clase' => $this->whenLoaded('productoClase'),
            'producto_subclase_id' => $this->producto_subclase_id,
            'producto_subclase' => $this->whenLoaded('productoSubclase'),
            'familia_sunat_id' => $this->familia_sunat_id,
            'familia_sunat' => $this->whenLoaded('familiaSunat'),
            'clase_sunat_id' => $this->clase_sunat_id,
            'clase_sunat' => $this->whenLoaded('claseSunat'),
            'tipo_afeccion_igv_id' => $this->tipo_afeccion_igv_id,
            'tipo_afeccion_igv' => $this->whenLoaded('tipoAfeccionIgv'),
            'tipo_calculo_isc_id' => $this->tipo_calculo_isc_id,
            'tipo_calculo_isc' => $this->whenLoaded('tipoCalculoIsc'),
            'precio_venta' => $this->precio_venta,
            'precio_venta_usd' => $this->precio_venta_usd,
            'costo_promedio' => $this->costo_promedio,
            'stock_minimo' => $this->stock_minimo,
            'stock_actual' => $this->stock_actual,
            'activo' => $this->activo,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
