<?php

namespace App\Modules\Inventory\Models;

use App\Models\User;
use App\Modules\Products\Models\Product;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InventoryMovement extends Model
{
    use HasUlids;
    use LogsActivity;

    protected $table = 'inventory_movimientos';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'tipo_movimiento',
        'referencia_tipo',
        'referencia_id',
        'cantidad',
        'precio_unitario',
        'valor_total',
        'saldo_anterior',
        'saldo_nuevo',
        'observaciones',
        'usuario_id',
        'fecha_movimiento',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'saldo_anterior' => 'decimal:2',
            'saldo_nuevo' => 'decimal:2',
            'fecha_movimiento' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo_movimiento', 'cantidad', 'saldo_nuevo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('inventory_movements');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
