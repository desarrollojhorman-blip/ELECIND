<?php

namespace App\Policies;

use App\Models\Borrador;
use App\Models\User;

class BorradorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('borradores.ver_todos') || $user->can('borradores.ver_propios');
    }

    public function view(User $user, Borrador $borrador): bool
    {
        if ($user->can('borradores.ver_todos')) {
            return true;
        }

        if (! $user->can('borradores.ver_propios')) {
            return false;
        }

        return $borrador->creado_por === $user->getKey();
    }

    public function create(User $user): bool
    {
        return $user->can('borradores.crear_movil') || $user->can('borradores.crear_web');
    }

    public function update(User $user, Borrador $borrador): bool
    {
        if ($borrador->estaConvertido()) {
            return false;
        }

        if ($borrador->creado_por === $user->getKey()) {
            return true;
        }

        return $user->can('borradores.modificar');
    }

    public function delete(User $user, Borrador $borrador): bool
    {
        if ($borrador->estaConvertido()) {
            return false;
        }

        if ($borrador->creado_por === $user->getKey()) {
            return true;
        }

        return $user->can('borradores.modificar');
    }

    public function convertir(User $user, Borrador $borrador): bool
    {
        if ($borrador->estaConvertido()) {
            return false;
        }

        return $user->can('borradores.convertir');
    }

    public function restore(User $user, Borrador $borrador): bool
    {
        return $user->can('borradores.modificar');
    }
}
