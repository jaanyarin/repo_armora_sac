<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductoSubclase extends Model
{
    use HasFactory, HasUlids, SoftDeletes, LogsActivity;

    protected $table = 'products_subclases';

    protected $fillable = [
        'clase_id',
        'codigo',
        'nombre',
        'slug',
        'descripcion',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['clase_id', 'codigo', 'nombre', 'slug', 'orden', 'activo', 'descripcion'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('products-subclases');
    }

    public function clase(): BelongsTo
    {
        return $this->belongsTo(ProductoClase::class, 'clase_id');
    }
}
