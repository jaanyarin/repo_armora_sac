<?php

namespace App\Modules\Purchases\Policies;

use App\Models\User;
use App\Modules\Purchases\Models\Compra;

class CompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-compras');
    }

    public function view(User $user, Compra $compra): bool
    {
        return $user->can('ver-compras');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-compras');
    }

    public function update(User $user, Compra $compra): bool
    {
        return $user->can('editar-compras');
    }

    public function delete(User $user, Compra $compra): bool
    {
        return $user->can('eliminar-compras') && $compra->estado === 'borrador';
    }

    public function confirmar(User $user, Compra $compra): bool
    {
        return $user->can('confirmar-compras') && $compra->estado === 'borrador';
    }

    public function anular(User $user, Compra $compra): bool
    {
        return $user->can('anular-compras') && $compra->estado !== 'anulada';
    }
}
