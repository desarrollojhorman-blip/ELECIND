<?php

namespace App\Policies;

use App\Models\Parte;
use App\Models\User;

/**
 * Permisos sobre partes de trabajo.
 *
 * Mismo espíritu que AlbaranPolicy:
 *   - `ver_todos` ve cualquier parte; `ver_propios` solo los suyos (creados
 *     por él o donde aparece como trabajador en alguna línea).
 *   - `modificar` puede editar partes de cualquiera; el creador siempre
 *     puede editar el suyo mientras esté abierto.
 *   - Bloqueo de edición/eliminación si el parte está `cerrado` o vinculado
 *     a un albarán firmado (lo gestiona la Fase 5).
 */
class PartePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('partes.ver_todos') || $user->can('partes.ver_propios');
    }

    public function view(User $user, Parte $parte): bool
    {
        if ($user->can('partes.ver_todos')) {
            return true;
        }

        if (! $user->can('partes.ver_propios')) {
            return false;
        }

        if ($parte->user_id === $user->getKey()) {
            return true;
        }

        return $parte->lineasPersonal()->where('user_id', $user->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('partes.crear_web') || $user->can('partes.crear_movil');
    }

    public function update(User $user, Parte $parte): bool
    {
        if (! $parte->esEditable()) {
            return false;
        }

        // El creador siempre puede editar su propio parte mientras esté abierto.
        if ($parte->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('partes.modificar');
    }

    public function delete(User $user, Parte $parte): bool
    {
        if (! $parte->esEditable()) {
            return false;
        }

        // El creador siempre puede eliminar su propio parte abierto.
        if ($parte->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('partes.eliminar');
    }

    public function restore(User $user, Parte $_parte): bool
    {
        return $user->can('partes.eliminar');
    }
}
