<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class SegmentoCliente extends Model
{
    protected $table = 'dim_segmento_sunat';

    protected $fillable = ['codigo', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
