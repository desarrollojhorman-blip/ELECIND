<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.gestionar');
    }

    public function view(User $user, Role $rol): bool
    {
        if (! $user->can('roles.gestionar')) {
            return false;
        }

        return $user->nivelMaximo() >= $rol->nivel;
    }

    public function create(User $user): bool
    {
        return $user->can('roles.gestionar');
    }

    public function update(User $user, Role $rol): bool
    {
        if (! $user->can('roles.gestionar')) {
            return false;
        }

        // El superadmin nunca es editable (ni siquiera por otro superadmin: protección extra).
        if ($rol->name === 'superadmin') {
            return false;
        }

        // Roles del sistema (administrador, trabajador, responsable): sólo superadmin.
        if ($rol->es_sistema && ! $user->hasRole('superadmin')) {
            return false;
        }

        return $user->nivelMaximo() >= $rol->nivel;
    }

    public function delete(User $user, Role $rol): bool
    {
        if (! $user->can('roles.gestionar')) {
            return false;
        }

        // Roles del sistema NUNCA se eliminan.
        if ($rol->es_sistema) {
            return false;
        }

        return $user->nivelMaximo() >= $rol->nivel;
    }

    /**
     * ¿Puede el usuario actual asignar el ámbito indicado al crear/editar un rol?
     * Solo el superadmin puede crear roles con ámbito "ambos".
     */
    public function puedeAsignarAmbito(User $user, string $ambito): bool
    {
        if ($ambito === 'ambos') {
            return $user->hasRole('superadmin');
        }

        return in_array($ambito, ['web', 'movil'], true);
    }
}
