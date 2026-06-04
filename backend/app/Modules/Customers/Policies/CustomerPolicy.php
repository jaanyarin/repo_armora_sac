<?php

namespace App\Modules\Customers\Policies;

use App\Models\User;
use App\Modules\Customers\Models\Customer;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-clientes');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('ver-clientes');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-clientes');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('editar-clientes');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('eliminar-clientes');
    }
}
