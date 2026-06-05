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

    /**
     * A-08: confirmar requiere permiso dedicado 'confirmar-ventas' y la venta
     * debe estar en borrador. El permiso está separado de 'editar-ventas' para
     * que un vendedor pueda editar el borrador (cambiar líneas, cliente) sin
     * poder confirmar (acción que dispara el descuento de stock y compromete
     * la integridad transaccional).
     */
    public function confirmar(User $user, Sale $sale): bool
    {
        return $user->can('confirmar-ventas') && $sale->estado === 'borrador';
    }
}
