<?php

namespace App\Policies;

use App\Models\Albaran;
use App\Models\AlbaranLineaPersonal;
use App\Models\Proyecto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

    /**
     * Eliminar (soft delete) — para el admin es "eliminar"; internamente envía a papelera.
     *
     * Bloqueo de integridad: si el usuario tiene proyectos vinculados (asignado
     * o responsable principal) o albaranes (creados o aparece en sus líneas),
     * NO se puede eliminar. Aplica a todos los roles. Si hay que romperlo, se
     * hace desde la BD.
     *
     * También se mantiene la regla de jerarquía: no se puede eliminar a alguien
     * con nivel mayor al propio, ni a uno mismo.
     */
    public function delete(User $user, User $target): Response|bool
    {
        if (! $user->can('usuarios.eliminar')) {
            return false;
        }

        if ($user->getKey() === $target->getKey()) {
            return Response::deny('No puedes eliminar tu propio usuario.');
        }

        if ($this->nivelDe($user) < $this->nivelDe($target)) {
            return false;
        }

        // Bloqueo de integridad por dependencias.
        $proyectosAsignados = $target->proyectos()->count();
        $proyectosResponsable = Proyecto::query()->where('responsable_principal_id', $target->getKey())->count();
        $proyectos = $proyectosAsignados + $proyectosResponsable;

        $albaranesCreados = Albaran::query()->where('creado_por', $target->getKey())->count();
        $albaranesEnLineas = AlbaranLineaPersonal::query()->where('trabajador_id', $target->getKey())->distinct('albaran_id')->count('albaran_id');
        $albaranes = $albaranesCreados + $albaranesEnLineas;

        if ($proyectos > 0 || $albaranes > 0) {
            return Response::deny(self::mensajeBloqueo($target, $proyectos, $albaranes));
        }

        return true;
    }

    /**
     * Restaurar desde papelera — protegido por el permiso `usuarios.gestionar_papelera`.
     * Por defecto solo lo tiene el superadmin (administrador excluido en el
     * seeder), pero técnicamente se puede dar a cualquier rol/usuario desde la
     * pantalla de Roles sin tocar código.
     *
     * Sigue aplicando la jerarquía: no se restaura a alguien con nivel mayor.
     */
    public function restore(User $user, User $target): bool
    {
        if (! $user->can('usuarios.gestionar_papelera')) {
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

        // Rol conocido: comprobar permiso específico.
        if ($permiso !== null) {
            return $user->can($permiso);
        }

        // Rol personalizado: permitir si el usuario tiene nivel suficiente.
        $rol = Role::query()
            ->where('name', $rolNombre)
            ->where('guard_name', 'web')
            ->first();

        return $user->nivelMaximo() >= ($rol?->nivel ?? PHP_INT_MAX);
    }

    /**
     * Mensaje del bloqueo por dependencias.
     * Conjuga proyecto/proyectos, albarán/albaranes y vinculado/vinculados.
     */
    private static function mensajeBloqueo(User $target, int $proyectos, int $albaranes): string
    {
        $partes = [];
        if ($proyectos > 0) {
            $partes[] = $proyectos.' '.($proyectos === 1 ? 'proyecto' : 'proyectos');
        }
        if ($albaranes > 0) {
            $partes[] = $albaranes.' '.($albaranes === 1 ? 'albarán' : 'albaranes');
        }
        $detalle = implode(' y ', $partes);
        $sufijo = ($proyectos + $albaranes) === 1 ? 'vinculado' : 'vinculados';
        $nombre = $target->username ?? 'usuario';

        return "No puedes eliminar el usuario «{$nombre}» porque tiene {$detalle} {$sufijo}.";
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
