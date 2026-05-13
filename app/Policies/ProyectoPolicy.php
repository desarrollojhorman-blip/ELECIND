<?php

namespace App\Policies;

use App\Models\Proyecto;
use App\Models\User;

class ProyectoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proyectos.ver');
    }

    public function view(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('proyectos.crear');
    }

    public function update(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.modificar');
    }

    public function delete(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.eliminar');
    }

    public function restore(User $user, Proyecto $proyecto): bool
    {
        return $user->can('proyectos.modificar');
    }
}
