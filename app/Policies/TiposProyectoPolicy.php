<?php

namespace App\Policies;

use App\Models\TiposProyecto;
use App\Models\User;

class TiposProyectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('grupos_proyecto.ver');
    }

    public function view(User $user, TiposProyecto $tipo): bool
    {
        return $user->can('grupos_proyecto.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('grupos_proyecto.crear');
    }

    public function update(User $user, TiposProyecto $tipo): bool
    {
        return $user->can('grupos_proyecto.modificar');
    }

    public function delete(User $user, TiposProyecto $tipo): bool
    {
        return $user->can('grupos_proyecto.eliminar');
    }

    public function restore(User $user, TiposProyecto $tipo): bool
    {
        return $user->can('grupos_proyecto.modificar');
    }
}
