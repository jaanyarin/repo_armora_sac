<?php

namespace App\Modules\Inventory\Models;

use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Stock extends Model
{
    use LogsActivity;

    protected $table = 'inventory_stock';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'cantidad_disponible',
        'cantidad_comprometida',
        'cantidad_minima',
        'cantidad_maxima',
        'ultima_actualizacion',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_disponible' => 'decimal:2',
            'cantidad_comprometida' => 'decimal:2',
            'cantidad_minima' => 'decimal:2',
            'cantidad_maxima' => 'decimal:2',
            'ultima_actualizacion' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['cantidad_disponible', 'cantidad_comprometida'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('inventory');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
