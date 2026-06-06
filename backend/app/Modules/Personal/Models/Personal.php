<?php

namespace App\Modules\Personal\Models;

use App\Models\Catalog\Almacen;
use App\Models\Catalog\Departamento;
use App\Models\Catalog\DocumentoIdentidad;
use App\Models\Catalog\EstadoCivil;
use App\Models\Catalog\ListaPrecio;
use App\Models\Catalog\Pais;
use App\Models\Catalog\Provincia;
use App\Models\Catalog\Sexo;
use App\Models\Catalog\Ubigeo;
use App\Models\User as BaseUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;

class Personal extends BaseUser
{
    use LogsActivity, SoftDeletes;

    protected $table = 'users';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'codigo', 'username', 'nombre_completo', 'email',
                'numero_documento', 'documento_identidad_id',
                'activo', 'telefono', 'telefono_celular',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function documentoIdentidad(): BelongsTo
    {
        return $this->belongsTo(DocumentoIdentidad::class, 'documento_identidad_id');
    }

    public function sexo(): BelongsTo
    {
        return $this->belongsTo(Sexo::class, 'sexo_id');
    }

    public function estadoCivil(): BelongsTo
    {
        return $this->belongsTo(EstadoCivil::class, 'estado_civil_id');
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }

    public function ubigeo(): BelongsTo
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo_id');
    }

    public function listasPrecios(): BelongsToMany
    {
        return $this->belongsToMany(ListaPrecio::class, 'personal_listas_precios', 'user_id', 'lista_precio_id')
            ->withTimestamps();
    }

    public function almacenes(): BelongsToMany
    {
        return $this->belongsToMany(Almacen::class, 'personal_almacenes', 'user_id', 'almacen_id')
            ->withTimestamps();
    }

    public function permisosDirectos(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'personal_permisos', 'user_id', 'permission_id')
            ->withTimestamps();
    }
}
