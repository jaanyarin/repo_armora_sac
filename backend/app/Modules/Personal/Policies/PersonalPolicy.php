<?php

namespace App\Modules\Personal\Policies;

use App\Models\User;
use App\Modules\Personal\Models\Personal;

class PersonalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver-personal');
    }

    public function view(User $user, Personal $personal): bool
    {
        return $user->can('ver-personal');
    }

    public function create(User $user): bool
    {
        return $user->can('crear-personal');
    }

    public function update(User $user, Personal $personal): bool
    {
        return $user->can('editar-personal');
    }

    public function delete(User $user, Personal $personal): bool
    {
        if ($personal->id === $user->id) {
            return false;
        }
        return $user->can('eliminar-personal');
    }
}
