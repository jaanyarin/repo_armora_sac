<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'codigo',
        'username',
        'name',
        'nombre_completo',
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'email',
        'documento_identidad_id',
        'numero_documento',
        'sexo_id',
        'estado_civil_id',
        'fecha_nacimiento',
        'pais_id',
        'telefono',
        'telefono_fijo',
        'telefono_celular',
        'departamento_id',
        'provincia_id',
        'ubigeo_id',
        'direccion',
        'referencia',
        'foto_path',
        'password',
        'password_changed_at',
        'ultimo_acceso',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
            'password_changed_at' => 'datetime',
            'fecha_nacimiento' => 'date',
        ];
    }

}
