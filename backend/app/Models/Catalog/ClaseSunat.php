<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaseSunat extends Model
{
    protected $table = 'dim_clase_sunat';

    protected $fillable = ['codigo', 'nombre', 'familia_id', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function familia(): BelongsTo
    {
        return $this->belongsTo(FamiliaSunat::class, 'familia_id');
    }
}
