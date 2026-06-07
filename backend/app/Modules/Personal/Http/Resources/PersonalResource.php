<?php

namespace App\Modules\Personal\Http\Resources;

use App\Modules\Personal\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Personal */
class PersonalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'username' => $this->username,
            'name' => $this->name,
            'nombre_completo' => $this->nombre_completo,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'nombres' => $this->nombres,
            'email' => $this->email,

            'documento_identidad_id' => $this->documento_identidad_id,
            'documento_identidad' => $this->whenLoaded('documentoIdentidad'),
            'numero_documento' => $this->numero_documento,

            'sexo_id' => $this->sexo_id,
            'sexo' => $this->whenLoaded('sexo'),
            'estado_civil_id' => $this->estado_civil_id,
            'estado_civil' => $this->whenLoaded('estadoCivil'),
            'fecha_nacimiento' => $this->fecha_nacimiento?->toDateString(),

            'pais_id' => $this->pais_id,
            'pais' => $this->whenLoaded('pais'),
            'telefono' => $this->telefono,
            'telefono_fijo' => $this->telefono_fijo,
            'telefono_celular' => $this->telefono_celular,

            'departamento_id' => $this->departamento_id,
            'departamento' => $this->whenLoaded('departamento'),
            'provincia_id' => $this->provincia_id,
            'provincia' => $this->whenLoaded('provincia'),
            'ubigeo_id' => $this->ubigeo_id,
            'ubigeo' => $this->whenLoaded('ubigeo'),
            'direccion' => $this->direccion,
            'referencia' => $this->referencia,

            'foto_path' => $this->foto_path,
            'foto_url' => $this->foto_path ? url('storage/' . $this->foto_path) : null,
            'activo' => $this->activo,
            'ultimo_acceso' => $this->ultimo_acceso?->toIso8601String(),
            'password_changed_at' => $this->password_changed_at?->toIso8601String(),

            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permisos_directos' => $this->whenLoaded('permisosDirectos', fn() => $this->permisosDirectos->pluck('name')),
            'permisos' => $this->whenLoaded('permisosDirectos', fn() => $this->permisosDirectos->pluck('id')->map(fn($v) => (int) $v)->values()),
            'listas_precios' => $this->whenLoaded('listasPrecios', fn() => $this->listasPrecios->map(fn($lp) => [
                'id' => $lp->id, 'nombre' => $lp->nombre,
            ])),
            'listas_precios_ids' => $this->whenLoaded('listasPrecios', fn() => $this->listasPrecios->pluck('id')->map(fn($v) => (int) $v)->values()),
            'almacenes' => $this->whenLoaded('almacenes', fn() => $this->almacenes->map(fn($a) => [
                'id' => $a->id, 'nombre' => $a->nombre,
            ])),
            'almacenes_ids' => $this->whenLoaded('almacenes', fn() => $this->almacenes->pluck('id')->map(fn($v) => (int) $v)->values()),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
