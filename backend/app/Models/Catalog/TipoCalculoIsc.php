<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class TipoCalculoIsc extends Model
{
    protected $table = 'dim_tipo_calculo_isc';

    protected $fillable = ['codigo_sunat', 'nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
