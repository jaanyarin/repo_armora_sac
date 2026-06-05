<?php

namespace App\Modules\Purchases\Services;

use App\Modules\Purchases\Models\Proveedor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProveedorService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Proveedor::query();

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'ilike', "%{$search}%")
                  ->orWhere('numero_documento', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%");
            });
        }

        if (array_key_exists('activo', $filters)) {
            $query->where('activo', $filters['activo']);
        }

        return $query->orderBy('nombre_completo')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function findById(string $id): ?Proveedor
    {
        return Proveedor::find($id);
    }

    public function create(array $data): Proveedor
    {
        if (empty($data['codigo'])) {
            $data['codigo'] = $this->generateCode();
        }
        return Proveedor::create($data);
    }

    public function update(Proveedor $proveedor, array $data): Proveedor
    {
        $proveedor->update($data);
        return $proveedor->fresh();
    }

    public function delete(Proveedor $proveedor): void
    {
        $proveedor->delete();
    }

    private function generateCode(): string
    {
        $next = (int) (Proveedor::withTrashed()->count() + 1);
        return sprintf('PROV-%05d', $next);
    }
}
