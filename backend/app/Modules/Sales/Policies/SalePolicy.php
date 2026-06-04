<?php

namespace App\Modules\Sales\Policies;

use App\Models\User;
use App\Modules\Sales\Models\Sale;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-ventas');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->can('ver-ventas');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-ventas');
    }

    public function update(User $user, Sale $sale): bool
    {
        if (!$user->can('ver-ventas')) {
            return false;
        }
        return !in_array($sale->estado, ['anulada', 'pagada'], true);
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->can('ver-ventas') && $sale->estado === 'borrador';
    }

    public function anular(User $user, Sale $sale): bool
    {
        return $user->can('anular-ventas') && $sale->estado !== 'anulada';
    }

    public function emitirNotaCredito(User $user, Sale $sale): bool
    {
        return $user->can('nota-credito')
            && in_array($sale->estado, ['confirmada', 'pagada', 'parcial'], true);
    }
}
