<?php

namespace App\Modules\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CreditNote extends Model
{
    use HasUlids;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'sales_notas_credito';

    protected $fillable = [
        'codigo',
        'venta_id',
        'nota_credito_tipo_id',
        'serie',
        'numero',
        'fecha_emision',
        'motivo',
        'subtotal',
        'igv',
        'total',
        'estado',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo', 'estado', 'total', 'motivo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('credit_notes');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
