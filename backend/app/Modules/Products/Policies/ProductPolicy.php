<?php

namespace App\Modules\Products\Policies;

use App\Models\User;
use App\Modules\Products\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-productos');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('ver-productos');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-productos');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('editar-productos');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('eliminar-productos');
    }
}
