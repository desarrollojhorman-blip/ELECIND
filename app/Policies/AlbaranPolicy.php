<?php

namespace App\Policies;

use App\Models\Albaran;
use App\Models\User;

class AlbaranPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('albaranes.ver_todos') || $user->can('albaranes.ver_propios');
    }

    public function view(User $user, Albaran $albaran): bool
    {
        if ($user->can('albaranes.ver_todos')) {
            return true;
        }

        if (! $user->can('albaranes.ver_propios')) {
            return false;
        }

        if ($albaran->creado_por === $user->getKey()) {
            return true;
        }

        return $albaran->lineasPersonal()->where('trabajador_id', $user->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('albaranes.crear_movil') || $user->can('albaranes.crear_web');
    }

    public function update(User $user, Albaran $albaran): bool
    {
        // En borrador, el creador siempre puede editar su propio parte.
        if ($albaran->estado->esEditable()) {
            if ($albaran->creado_por === $user->getKey()) {
                return true;
            }

            return $user->can('albaranes.modificar');
        }

        // Una vez fuera de borrador, requiere permiso especial.
        return $user->can('albaranes.modificar_terminado');
    }

    public function delete(User $user, Albaran $albaran): bool
    {
        if (! $albaran->estado->esEditable()) {
            return false;
        }

        // El creador siempre puede eliminar su propio borrador.
        if ($albaran->creado_por === $user->getKey()) {
            return true;
        }

        return $user->can('albaranes.modificar') || $user->can('albaranes.modificar_terminado');
    }

    public function firmar(User $user, Albaran $albaran): bool
    {
        if (! $user->can('albaranes.firmar')) {
            return false;
        }

        // Creador del parte
        if ($albaran->creado_por === $user->getKey()) {
            return true;
        }

        // Firmante trabajador asignado explícitamente desde la web
        if ($albaran->firma_trabajador_user_id !== null
            && $albaran->firma_trabajador_user_id === $user->getKey()) {
            return true;
        }

        // Responsable asignado
        return $albaran->responsable_id === $user->getKey();
    }

    public function restore(User $user, Albaran $albaran): bool
    {
        return $user->can('albaranes.modificar_terminado');
    }
}
