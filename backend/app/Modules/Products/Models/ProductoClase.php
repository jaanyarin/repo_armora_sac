<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductoClase extends Model
{
    use HasFactory, HasUlids, SoftDeletes, LogsActivity;

    protected $table = 'products_clases';

    protected $fillable = [
        'codigo',
        'nombre',
        'slug',
        'descripcion',
        'licor',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'licor' => 'boolean',
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo', 'nombre', 'slug', 'licor', 'orden', 'activo', 'descripcion'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('products-clases');
    }

    public function subclases(): HasMany
    {
        return $this->hasMany(ProductoSubclase::class, 'clase_id');
    }

    public function subclasesActivas(): HasMany
    {
        return $this->subclases()->where('activo', true);
    }
}
