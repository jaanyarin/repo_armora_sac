<?php

namespace App\Modules\Customers\Services;

use App\Modules\Customers\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CustomerService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return Customer::query()
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where(function($q) use ($s) {
                $q->where('nombre_completo', 'ilike', "%{$s}%")
                  ->orWhere('numero_documento', 'ilike', "%{$s}%")
                  ->orWhere('codigo', 'ilike', "%{$s}%");
            }))
            ->when($filters['activo'] ?? null, fn($q, $v) => $q->where('activo', $v === 'true' || $v === '1'))
            ->with(['tipoCliente', 'segmento', 'ubigeo'])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Customer
    {
        return Customer::with(['tipoCliente', 'segmento', 'ubigeo.provincia.departamento'])->find($id);
    }

    public function create(array $data): Customer
    {
        $data['codigo'] = $data['codigo'] ?? $this->generateCode();
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    private function generateCode(): string
    {
        $prefix = 'CLI';
        $last = Customer::withTrashed()->max('id') ?? 0;
        return $prefix . '-' . Str::padLeft($last + 1, 5, '0');
    }
}
