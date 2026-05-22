<?php

namespace App\Policies;

use App\Models\Concepto;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConceptoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('conceptos.ver');
    }

    public function view(User $user, Concepto $concepto): bool
    {
        return $user->can('conceptos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('conceptos.crear');
    }

    public function update(User $user, Concepto $concepto): bool
    {
        return $user->can('conceptos.modificar');
    }

    /**
     * Eliminar (soft delete) — envía a papelera.
     *
     * Bloqueo solo si el concepto está usado en albaranes (no contando los
     * que ya están en papelera). Estar asociado a proyectos NO bloquea:
     * el pivot `proyecto_concepto` es configuración, no historial. En ese
     * caso el componente Livewire muestra un aviso del nº de proyectos.
     *
     * Para «retirar» un concepto que ya está en albaranes, la vía correcta
     * es Desactivar (activo = false) — el mensaje del bloqueo lo sugiere.
     */
    public function delete(User $user, Concepto $concepto): Response|bool
    {
        if (! $user->can('conceptos.eliminar')) {
            return false;
        }

        $albaranes = $concepto->albaranes()->count();

        if ($albaranes > 0) {
            $palabra = $albaranes === 1 ? 'albarán' : 'albaranes';

            return Response::deny(
                "No puedes eliminar el concepto «{$concepto->nombre}» porque está usado en {$albaranes} {$palabra}. "
                ."Si ya no lo usas, desactívalo desde Editar para que deje de aparecer en los selectores sin perder el histórico."
            );
        }

        return true;
    }

    /**
     * Restaurar desde papelera — protegido por `conceptos.gestionar_papelera`.
     * Por defecto solo superadmin.
     */
    public function restore(User $user, Concepto $concepto): bool
    {
        return $user->can('conceptos.gestionar_papelera');
    }
}
