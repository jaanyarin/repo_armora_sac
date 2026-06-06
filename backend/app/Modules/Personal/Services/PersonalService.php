<?php

namespace App\Modules\Personal\Services;

use App\Models\User;
use App\Modules\Personal\Models\Personal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PersonalService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Personal::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('nombre_completo', 'ilike', "%{$s}%")
                  ->orWhere('username', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%")
                  ->orWhere('numero_documento', 'ilike', "%{$s}%")
                  ->orWhere('codigo', 'ilike', "%{$s}%");
            }))
            ->when(isset($filters['activo']), fn($q) => $q->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN)))
            ->with(['documentoIdentidad', 'sexo', 'estadoCivil', 'pais', 'ubigeo.provincia.departamento', 'roles', 'listasPrecios', 'almacenes'])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Personal
    {
        return Personal::with([
            'documentoIdentidad', 'sexo', 'estadoCivil', 'pais',
            'departamento', 'provincia', 'ubigeo',
            'roles.permissions', 'permisosDirectos', 'listasPrecios', 'almacenes',
        ])->find($id);
    }

    public function create(array $data): Personal
    {
        return DB::transaction(function () use ($data) {
            $data['codigo'] = $data['codigo'] ?? $this->generateCode();
            $data['password_changed_at'] = now();
            $data['activo'] = $data['activo'] ?? true;

            $data = $this->resolveNombreCompleto($data);
            $data['name'] = $data['nombre_completo'];

            $personal = Personal::create($data);

            $this->syncRelations($personal, $data);

            return $personal->fresh([
                'documentoIdentidad', 'sexo', 'estadoCivil', 'pais', 'ubigeo',
                'roles', 'listasPrecios', 'almacenes', 'permisosDirectos',
            ]);
        });
    }

    public function update(Personal $personal, array $data): Personal
    {
        return DB::transaction(function () use ($personal, $data) {
            if (!empty($data['password'])) {
                $data['password_changed_at'] = now();
            } else {
                unset($data['password']);
            }

            $data = $this->resolveNombreCompleto($data, $personal);
            $data['name'] = $data['nombre_completo'];

            $personal->update($data);
            $this->syncRelations($personal, $data);

            return $personal->fresh([
                'documentoIdentidad', 'sexo', 'estadoCivil', 'pais', 'ubigeo',
                'roles', 'listasPrecios', 'almacenes', 'permisosDirectos',
            ]);
        });
    }

    public function delete(Personal $personal): void
    {
        $personal->delete();
    }

    public function uploadPhoto(Personal $personal, UploadedFile $file): Personal
    {
        if ($personal->foto_path && Storage::disk('public')->exists($personal->foto_path)) {
            Storage::disk('public')->delete($personal->foto_path);
        }

        $path = $file->store("personal/{$personal->id}", 'public');
        $personal->update(['foto_path' => $path]);

        return $personal->fresh();
    }

    public function resetPhoto(Personal $personal): Personal
    {
        if ($personal->foto_path && Storage::disk('public')->exists($personal->foto_path)) {
            Storage::disk('public')->delete($personal->foto_path);
        }

        $personal->update(['foto_path' => null]);

        return $personal->fresh();
    }

    public function getPhotoPath(Personal $personal): ?string
    {
        if ($personal->foto_path && Storage::disk('public')->exists($personal->foto_path)) {
            return Storage::disk('public')->path($personal->foto_path);
        }
        return null;
    }

    public function getRolesDisponibles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function getPermisosAgrupados(): Collection
    {
        return Permission::orderBy('modulo')->orderBy('name')->get()->groupBy('modulo');
    }

    private function syncRelations(Personal $personal, array $data): void
    {
        if (array_key_exists('roles', $data)) {
            $roles = $data['roles'] ?? [];
            $personal->syncRoles($roles);
        }

        if (array_key_exists('permisos', $data)) {
            $permisos = $data['permisos'] ?? [];
            $personal->permisosDirectos()->sync($permisos);
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        if (array_key_exists('listas_precios', $data)) {
            $personal->listasPrecios()->sync($data['listas_precios'] ?? []);
        }

        if (array_key_exists('almacenes', $data)) {
            $personal->almacenes()->sync($data['almacenes'] ?? []);
        }
    }

    private function generateCode(): string
    {
        $prefix = 'PER';
        $last = Personal::withTrashed()->max('id') ?? 0;
        return $prefix . '-' . Str::padLeft($last + 1, 5, '0');
    }

    private function resolveNombreCompleto(array $data, ?Personal $existing = null): array
    {
        $apPaterno = $data['apellido_paterno'] ?? $existing?->apellido_paterno;
        $apMaterno = $data['apellido_materno'] ?? $existing?->apellido_materno;
        $nombres = $data['nombres'] ?? $existing?->nombres;

        $parts = array_filter([$apPaterno, $apMaterno, $nombres], fn($v) => is_string($v) && trim($v) !== '');
        $data['nombre_completo'] = trim(implode(' ', $parts));

        return $data;
    }
}
