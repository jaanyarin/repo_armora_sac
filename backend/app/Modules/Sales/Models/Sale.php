<?php

namespace App\Modules\Sales\Models;

use App\Models\User;
use App\Modules\Customers\Models\Customer;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    use HasUlids;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'sales_ventas';

    protected $fillable = [
        'codigo',
        'cliente_id',
        'usuario_id',
        'documento_tipo_id',
        'serie',
        'numero',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda_id',
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
            ->logOnly(['codigo', 'estado', 'total', 'cliente_id', 'fecha_emision'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('sales');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'venta_id');
    }

    public function notasCredito(): HasMany
    {
        return $this->hasMany(CreditNote::class, 'venta_id');
    }
}
