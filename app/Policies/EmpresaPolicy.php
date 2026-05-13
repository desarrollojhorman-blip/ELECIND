<?php

namespace App\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function view(User $user, Empresa $empresa): bool
    {
        return $user->can('configuracion.empresa');
    }

    public function update(User $user, Empresa $empresa): bool
    {
        return $user->can('configuracion.empresa');
    }
}
