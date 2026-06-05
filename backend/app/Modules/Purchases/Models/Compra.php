<?php

namespace App\Modules\Purchases\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Compra extends Model
{
    use HasUlids;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'purchases_compras';

    protected $fillable = [
        'codigo',
        'proveedor_id',
        'usuario_id',
        'documento_tipo_id',
        'serie',
        'numero',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda_id',
        'almacen_id',
        'subtotal',
        'descuento_global',
        'igv',
        'isc',
        'total',
        'saldo_pendiente',
        'estado',
        'observaciones',
        'origen',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'decimal:2',
            'descuento_global' => 'decimal:2',
            'igv' => 'decimal:2',
            'isc' => 'decimal:2',
            'total' => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo', 'estado', 'total', 'proveedor_id', 'fecha_emision'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('purchases');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CompraItem::class, 'compra_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Catalog\Almacen::class, 'almacen_id');
    }
}
