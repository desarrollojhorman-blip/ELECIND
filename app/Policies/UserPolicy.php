<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('usuarios.ver_todos');
    }

    public function view(User $user, User $target): bool
    {
        if (! $user->can('usuarios.ver_todos')) {
            return false;
        }

        return $this->nivelDe($user) >= $this->nivelDe($target);
    }

    public function create(User $user): bool
    {
        return $user->can('usuarios.crear_superadmin')
            || $user->can('usuarios.crear_administrador')
            || $user->can('usuarios.crear_trabajador')
            || $user->can('usuarios.crear_responsable');
    }

    public function update(User $user, User $target): bool
    {
        if (! $user->can('usuarios.modificar')) {
            return false;
        }

        if ($user->getKey() === $target->getKey()) {
            return true;
        }

        return $this->nivelDe($user) >= $this->nivelDe($target);
    }

    public function delete(User $user, User $target): bool
    {
        if (! $user->can('usuarios.eliminar')) {
            return false;
        }

        if ($user->getKey() === $target->getKey()) {
            return false;
        }

        return $this->nivelDe($user) >= $this->nivelDe($target);
    }

    public function restore(User $user, User $target): bool
    {
        if (! $user->can('usuarios.eliminar')) {
            return false;
        }

        return $this->nivelDe($user) >= $this->nivelDe($target);
    }

    public function puedeAsignarRol(User $user, string $rolNombre): bool
    {
        $permiso = match ($rolNombre) {
            'superadmin' => 'usuarios.crear_superadmin',
            'administrador' => 'usuarios.crear_administrador',
            'trabajador' => 'usuarios.crear_trabajador',
            'responsable' => 'usuarios.crear_responsable',
            default => null,
        };

        // Rol conocido: comprobar permiso específico
        if ($permiso !== null) {
            return $user->can($permiso);
        }

        // Rol personalizado (creado dinámicamente): permitir si el usuario
        // tiene nivel suficiente para gestionar ese rol.
        $rol = Role::query()
            ->where('name', $rolNombre)
            ->where('guard_name', 'web')
            ->first();

        return $user->nivelMaximo() >= ($rol?->nivel ?? PHP_INT_MAX);
    }

    private function nivelDe(User $user): int
    {
        $roles = $user->roles;

        if ($roles->isEmpty()) {
            return 0;
        }

        return (int) $roles->max('nivel');
    }
}
