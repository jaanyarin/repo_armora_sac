<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'dim_almacen';

    protected $fillable = [
        'codigo', 'nombre', 'direccion', 'telefono', 'principal', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
            'activo' => 'boolean',
        ];
    }
}
