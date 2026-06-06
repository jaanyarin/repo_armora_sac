<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class DocumentoIdentidad extends Model
{
    protected $table = 'dim_documento_identidad';

    protected $fillable = [
        'codigo', 'nombre', 'longitud', 'regex', 'pais_codigo', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
