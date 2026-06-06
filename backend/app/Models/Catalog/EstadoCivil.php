<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class EstadoCivil extends Model
{
    protected $table = 'dim_estado_civil';

    protected $fillable = ['nombre'];

    public $timestamps = false;
}
