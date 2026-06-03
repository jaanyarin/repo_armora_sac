<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    protected $table = 'dim_moneda';

    protected $fillable = ['codigo', 'nombre', 'simbolo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
