<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class DocumentoSimbolo extends Model
{
    protected $table = 'dim_documento_simbolo';

    protected $fillable = ['codigo_sunat', 'nombre', 'simbolo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
