<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoTipo extends Model
{
    protected $table = 'dim_documento_tipo';

    protected $fillable = ['codigo_sunat', 'nombre', 'simbolo_id', 'requiere_ruc', 'activo'];

    protected function casts(): array
    {
        return ['requiere_ruc' => 'boolean', 'activo' => 'boolean'];
    }

    public function simbolo(): BelongsTo
    {
        return $this->belongsTo(DocumentoSimbolo::class, 'simbolo_id');
    }
}
