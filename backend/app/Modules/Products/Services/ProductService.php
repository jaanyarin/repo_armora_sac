<?php

namespace App\Modules\Products\Services;

use App\Modules\Products\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('nombre', 'ilike', "%{$s}%")
                  ->orWhere('codigo', 'ilike', "%{$s}%")
                  ->orWhere('codigo_sunat', 'ilike', "%{$s}%");
            }))
            ->when($filters['activo'] ?? null, fn($q, $v) => $q->where('activo', $v === 'true' || $v === '1'))
            ->when($filters['unidad_medida_id'] ?? null, fn($q, $v) => $q->where('unidad_medida_id', $v))
            ->when($filters['producto_clase_id'] ?? null, fn($q, $v) => $q->where('producto_clase_id', $v))
            ->with(['unidadMedida', 'productoClase', 'tipoAfeccionIgv'])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Product
    {
        return Product::with([
            'unidadMedida', 'productoClase', 'productoSubclase',
            'familiaSunat', 'claseSunat', 'tipoAfeccionIgv', 'tipoCalculoIsc',
        ])->find($id);
    }

    public function create(array $data): Product
    {
        $data['codigo'] = $data['codigo'] ?? $this->generateCode();
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    private function generateCode(): string
    {
        $prefix = 'PROD';
        $last = Product::withTrashed()->max('id') ?? 0;
        return $prefix . '-' . Str::padLeft($last + 1, 5, '0');
    }
}
