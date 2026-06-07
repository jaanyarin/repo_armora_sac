<?php

namespace App\Modules\Products\Policies;

use App\Models\User;
use App\Modules\Products\Models\ProductoClase;

class ProductoClasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-clases');
    }

    public function view(User $user, ProductoClase $clase): bool
    {
        return $user->can('ver-clases');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-clases');
    }

    public function update(User $user, ProductoClase $clase): bool
    {
        return $user->can('editar-clases');
    }

    public function delete(User $user, ProductoClase $clase): bool
    {
        return $user->can('eliminar-clases');
    }

    public function reorder(User $user): bool
    {
        return $user->can('editar-clases');
    }
}
