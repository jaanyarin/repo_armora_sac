<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoSubclase extends Model
{
    protected $table = 'dim_producto_subclase';

    protected $fillable = ['clase_id', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function clase(): BelongsTo
    {
        return $this->belongsTo(ProductoClase::class, 'clase_id');
    }
}
