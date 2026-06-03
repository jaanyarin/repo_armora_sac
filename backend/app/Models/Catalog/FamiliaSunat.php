<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamiliaSunat extends Model
{
    protected $table = 'dim_familia_sunat';

    protected $fillable = ['codigo', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function clases(): HasMany
    {
        return $this->hasMany(ClaseSunat::class, 'familia_id');
    }
}
