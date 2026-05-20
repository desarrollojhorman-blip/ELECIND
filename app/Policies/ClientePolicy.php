<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clientes.ver');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('clientes.crear');
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.modificar');
    }

    /**
     * Eliminar (soft delete) — para el admin es "eliminar"; internamente envía a papelera.
     *
     * Bloqueo de integridad: si el cliente tiene proyectos o usuarios vinculados
     * (no contando los que ya están en papelera), NO se puede eliminar.
     * La regla aplica a todos los roles, incluido superadmin: si hace falta
     * romperla, se hace desde la BD.
     */
    public function delete(User $user, Cliente $cliente): Response|bool
    {
        if (! $user->can('clientes.eliminar')) {
            return false;
        }

        $proyectos = $cliente->proyectos()->count();
        $usuarios = $cliente->usuarios()->count();

        if ($proyectos > 0 || $usuarios > 0) {
            return Response::deny(self::mensajeBloqueo($cliente, $proyectos, $usuarios));
        }

        return true;
    }

    /**
     * Restaurar desde papelera — protegido por el permiso `clientes.gestionar_papelera`.
     * Por defecto solo lo tiene el superadmin (administrador excluido en el
     * seeder), pero técnicamente se puede dar a cualquier rol/usuario desde
     * la pantalla de Roles sin tocar código.
     */
    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.gestionar_papelera');
    }

    /**
     * Mensaje del bloqueo por dependencias.
     * Conjuga proyecto/proyectos, usuario/usuarios y vinculado/vinculados.
     */
    private static function mensajeBloqueo(Cliente $cliente, int $proyectos, int $usuarios): string
    {
        $partes = [];
        if ($proyectos > 0) {
            $partes[] = $proyectos.' '.($proyectos === 1 ? 'proyecto' : 'proyectos');
        }
        if ($usuarios > 0) {
            $partes[] = $usuarios.' '.($usuarios === 1 ? 'usuario' : 'usuarios');
        }
        $detalle = implode(' y ', $partes);
        $sufijo = ($proyectos + $usuarios) === 1 ? 'vinculado' : 'vinculados';

        return "No puedes eliminar el cliente «{$cliente->nombre}» porque tiene {$detalle} {$sufijo}.";
    }
}
