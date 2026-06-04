<?php

namespace App\Modules\Sales\Models;

use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SaleItem extends Model
{
    use HasUlids;
    use LogsActivity;

    protected $table = 'sales_venta_items';

    protected $fillable = [
        'venta_id',
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
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'descuento_linea' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cantidad', 'precio_unitario', 'total', 'producto_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sales_items');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
