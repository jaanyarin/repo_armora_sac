<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class TipoCliente extends Model
{
    protected $table = 'dim_tipo_cliente';

    protected $fillable = ['nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
