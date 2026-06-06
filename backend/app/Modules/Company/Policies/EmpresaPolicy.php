<?php

namespace App\Modules\Company\Policies;

use App\Models\User;
use App\Modules\Company\Models\EmpresaConfig;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmpresaPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->can('ver-configuracion');
    }

    public function update(User $user): bool
    {
        return $user->can('configurar-empresa');
    }
}
