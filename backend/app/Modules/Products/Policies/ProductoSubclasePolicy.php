<?php

namespace App\Modules\Products\Policies;

use App\Models\User;
use App\Modules\Products\Models\ProductoSubclase;

class ProductoSubclasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-subclases');
    }

    public function view(User $user, ProductoSubclase $subclase): bool
    {
        return $user->can('ver-subclases');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-subclases');
    }

    public function update(User $user, ProductoSubclase $subclase): bool
    {
        return $user->can('editar-subclases');
    }

    public function delete(User $user, ProductoSubclase $subclase): bool
    {
        return $user->can('eliminar-subclases');
    }

    public function reorder(User $user): bool
    {
        return $user->can('editar-subclases');
    }
}
