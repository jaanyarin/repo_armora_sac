<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class TipoAfeccionIgv extends Model
{
    protected $table = 'dim_tipo_afeccion_igv';

    protected $fillable = ['codigo_sunat', 'nombre', 'tributo_asociado', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
