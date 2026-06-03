<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class NotaCreditoTipo extends Model
{
    protected $table = 'dim_nota_credito_tipo';

    protected $fillable = ['codigo_sunat', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
