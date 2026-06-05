<?php

namespace App\Modules\Inventory\Policies;

use App\Models\User;

/**
 * A-11: Policy para Inventory. Los métodos no reciben modelo (no hay
 * recurso "single inventory" a proteger), solo verifican permisos por
 * capacidad (consultar stock, ajustar, kardex).
 */
class InventoryPolicy
{
    public function viewStock(User $user): bool
    {
        return $user->can('ver-stock');
    }

    public function viewKardex(User $user): bool
    {
        return $user->can('kardex');
    }

    public function adjust(User $user): bool
    {
        return $user->can('ajustar-stock');
    }
}
