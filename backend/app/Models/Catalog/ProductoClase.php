<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductoClase extends Model
{
    protected $table = 'dim_producto_clase';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function subclases(): HasMany
    {
        return $this->hasMany(ProductoSubclase::class, 'clase_id');
    }
}
