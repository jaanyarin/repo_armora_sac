<?php

namespace App\Modules\Products\Models;

use App\Models\Catalog\ClaseSunat;
use App\Models\Catalog\FamiliaSunat;
use App\Models\Catalog\ProductoClase;
use App\Models\Catalog\ProductoSubclase;
use App\Models\Catalog\TipoAfeccionIgv;
use App\Models\Catalog\TipoCalculoIsc;
use App\Models\Catalog\UnidadMedida;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'codigo_sunat',
        'nombre',
        'descripcion',
        'unidad_medida_id',
        'producto_clase_id',
        'producto_subclase_id',
        'familia_sunat_id',
        'clase_sunat_id',
        'tipo_afeccion_igv_id',
        'tipo_calculo_isc_id',
        'precio_venta',
        'precio_venta_usd',
        'costo_promedio',
        'stock_minimo',
        'stock_actual',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'precio_venta_usd' => 'decimal:2',
            'costo_promedio' => 'decimal:2',
            'stock_minimo' => 'decimal:2',
            'stock_actual' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function productoClase(): BelongsTo
    {
        return $this->belongsTo(ProductoClase::class, 'producto_clase_id');
    }

    public function productoSubclase(): BelongsTo
    {
        return $this->belongsTo(ProductoSubclase::class, 'producto_subclase_id');
    }

    public function familiaSunat(): BelongsTo
    {
        return $this->belongsTo(FamiliaSunat::class, 'familia_sunat_id');
    }

    public function claseSunat(): BelongsTo
    {
        return $this->belongsTo(ClaseSunat::class, 'clase_sunat_id');
    }

    public function tipoAfeccionIgv(): BelongsTo
    {
        return $this->belongsTo(TipoAfeccionIgv::class, 'tipo_afeccion_igv_id');
    }

    public function tipoCalculoIsc(): BelongsTo
    {
        return $this->belongsTo(TipoCalculoIsc::class, 'tipo_calculo_isc_id');
    }
}
