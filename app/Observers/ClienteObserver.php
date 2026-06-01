<?php

namespace App\Observers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ClienteObserver
{
    /**
     * Cuando un usuario "scoped" (p. ej. Jefe de equipo) crea un cliente nuevo,
     * lo auto-añadimos a su lista de clientes gestionados.
     *
     * Esto evita la paradoja del scope: "creo un cliente pero no puedo verlo
     * porque no está entre los que me asignaron". Si lo creo yo, queda bajo
     * mi gestión automáticamente.
     *
     * Solo aplica a usuarios cuyo rol tiene solo_clientes_asignados=true.
     * Para admins/superadmin (ven todo) no hace nada porque no tienen scope.
     */
    public function created(Cliente $cliente): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        // idsClientesGestionados() devuelve null si el usuario NO está scoped
        // (admin, superadmin, o jefe con "todos los clientes" activado).
        // En esos casos no hay que hacer nada: ya verá el cliente.
        if ($user->idsClientesGestionados() === null) {
            return;
        }

        // El usuario está scoped y acaba de crear un cliente: enlazarlo.
        $user->clientesGestionados()->syncWithoutDetaching([$cliente->id]);
    }
}
