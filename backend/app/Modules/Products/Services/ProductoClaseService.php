<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\ProductoClase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductoClaseService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = ProductoClase::query()->withCount('subclases');

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }

        if (array_key_exists('activo', $filters) && $filters['activo'] !== '' && $filters['activo'] !== null) {
            $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('licor', $filters) && $filters['licor'] !== '' && $filters['licor'] !== null) {
            $query->where('licor', filter_var($filters['licor'], FILTER_VALIDATE_BOOLEAN));
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

    public function findById(string $id): ?ProductoClase
    {
        return ProductoClase::withCount('subclases')->find($id);
    }

    public function create(array $data): ProductoClase
    {
        return DB::transaction(function () use ($data) {
            $data['codigo'] = $data['codigo'] ?? $this->generateCode();
            $data['slug'] = $data['slug'] ?? $this->generateSlug($data['nombre']);
            $data['activo'] = $data['activo'] ?? true;
            $data['licor'] = $data['licor'] ?? false;
            $data['orden'] = $data['orden'] ?? $this->nextOrden();

            return ProductoClase::create($data);
        });
    }

    public function update(ProductoClase $clase, array $data): ProductoClase
    {
        return DB::transaction(function () use ($clase, $data) {
            if (isset($data['nombre']) && empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['nombre'], $clase->id);
            }
            $clase->update($data);
            return $clase->fresh(['subclases']);
        });
    }

    public function delete(ProductoClase $clase): void
    {
        DB::transaction(function () use ($clase) {
            $clase->subclases()->delete();
            $clase->delete();
        });
    }

    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                ProductoClase::where('id', $id)->update(['orden' => $index + 1]);
            }
        });
    }

    public function countSubclases(ProductoClase $clase, bool $onlyActive = true): int
    {
        $query = $clase->subclases();
        if ($onlyActive) {
            $query->where('activo', true);
        }
        return $query->count();
    }

    private function generateCode(): string
    {
        $last = (int) (ProductoClase::withTrashed()->max('orden') ?? 0);
        $next = $last + 1;
        return 'CLS-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function generateSlug(string $nombre, ?string $ignoreId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $counter = 1;
        $query = ProductoClase::query()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        while ($query->exists()) {
            $slug = $base . '-' . $counter;
            $query = ProductoClase::query()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            $counter++;
        }
        return $slug;
    }

    private function nextOrden(): int
    {
        return ((int) (ProductoClase::max('orden') ?? 0)) + 1;
    }
}
