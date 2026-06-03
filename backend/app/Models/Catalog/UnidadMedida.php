<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'dim_unidad_medida';

    protected $fillable = ['codigo_sunat', 'nombre', 'simbolo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
