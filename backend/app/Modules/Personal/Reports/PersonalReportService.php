<?php

namespace App\Modules\Personal\Reports;

use App\Modules\Personal\Models\Personal;
use Illuminate\Database\Eloquent\Collection;

class PersonalReportService
{
    public function getPersonalActivo(): Collection
    {
        return Personal::query()
            ->where('activo', true)
            ->with(['documentoIdentidad', 'sexo', 'estadoCivil', 'roles', 'listasPrecios', 'almacenes'])
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();
    }

    public function findForFicha(int $id): ?Personal
    {
        return Personal::withTrashed()
            ->with([
                'documentoIdentidad', 'sexo', 'estadoCivil', 'pais',
                'departamento', 'provincia', 'ubigeo',
                'roles', 'permisosDirectos', 'listasPrecios', 'almacenes',
            ])
            ->find($id);
    }
}
