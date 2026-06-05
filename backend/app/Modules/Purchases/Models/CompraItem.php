<?php

namespace App\Modules\Purchases\Models;

use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompraItem extends Model
{
    use HasUlids;

    protected $table = 'purchases_compra_items';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'unidad_medida_id',
        'numero_linea',
        'cantidad',
        'precio_unitario',
        'descuento_linea',
        'subtotal',
        'igv',
        'total',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'numero_linea' => 'integer',
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'descuento_linea' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\UnidadMedida::class, 'unidad_medida_id');
    }
}
