<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    protected $table = 'dim_sexo';

    protected $fillable = ['nombre'];

    public $timestamps = false;
}
