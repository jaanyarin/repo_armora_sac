<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\ProductoClase;
use App\Modules\Products\Models\ProductoSubclase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductoSubclaseService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = ProductoSubclase::query()->with('clase:id,nombre');

        if (!empty($filters['clase_id'])) {
            $query->where('clase_id', $filters['clase_id']);
        }

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%")
                  ->orWhereHas('clase', fn($c) => $c->where('nombre', 'ilike', "%{$search}%"));
            });
        }

        if (array_key_exists('activo', $filters) && $filters['activo'] !== '' && $filters['activo'] !== null) {
            $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = $filters['sort_by'] ?? 'orden';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSorts = ['orden', 'nombre', 'codigo', 'created_at'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'orden';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)
            ->orderBy('nombre')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function findById(string $id): ?ProductoSubclase
    {
        return ProductoSubclase::with('clase:id,nombre,codigo')->find($id);
    }

    public function create(array $data): ProductoSubclase
    {
        return DB::transaction(function () use ($data) {
            $claseId = $data['clase_id'];
            $clase = ProductoClase::find($claseId);
            if (!$clase) {
                throw new \InvalidArgumentException('La clase seleccionada no existe.');
            }

            $data['codigo'] = $data['codigo'] ?? $this->generateCode();
            $data['slug'] = $data['slug'] ?? $this->generateSlug($data['nombre'], $claseId);
            $data['activo'] = $data['activo'] ?? true;
            $data['orden'] = $data['orden'] ?? $this->nextOrdenForClase($claseId);

            return ProductoSubclase::create($data);
        });
    }

    public function update(ProductoSubclase $subclase, array $data): ProductoSubclase
    {
        return DB::transaction(function () use ($subclase, $data) {
            if (isset($data['nombre']) && empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['nombre'], $subclase->clase_id, $subclase->id);
            }
            $subclase->update($data);
            return $subclase->fresh(['clase']);
        });
    }

    public function delete(ProductoSubclase $subclase): void
    {
        $subclase->delete();
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                ProductoSubclase::where('id', $id)->update(['orden' => $index + 1]);
            }
        });
    }

    public function countByClase(string $claseId, bool $onlyActive = true): int
    {
        $query = ProductoSubclase::where('clase_id', $claseId);
        if ($onlyActive) {
            $query->where('activo', true);
        }
        return $query->count();
    }

    private function generateCode(): string
    {
        $next = (int) (ProductoSubclase::withTrashed()->count() + 1);
        return 'SCL-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function generateSlug(string $nombre, string $claseId, ?string $ignoreId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $counter = 1;
        $query = ProductoSubclase::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        while ($query->exists()) {
            $slug = $base . '-' . $counter;
            $query = ProductoSubclase::query()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $counter++;
        }
        return $slug;
    }

    private function nextOrdenForClase(string $claseId): int
    {
        return ((int) (ProductoSubclase::where('clase_id', $claseId)->max('orden') ?? 0)) + 1;
    }
}
