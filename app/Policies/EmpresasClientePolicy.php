<?php

namespace App\Policies;

use App\Models\EmpresasCliente;
use App\Models\User;

class EmpresasClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clientes.ver');
    }

    public function view(User $user, EmpresasCliente $empresa): bool
    {
        return $user->can('clientes.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('clientes.crear');
    }

    public function update(User $user, EmpresasCliente $empresa): bool
    {
        return $user->can('clientes.modificar');
    }

    public function delete(User $user, EmpresasCliente $empresa): bool
    {
        return $user->can('clientes.eliminar');
    }

    public function restore(User $user, EmpresasCliente $empresa): bool
    {
        return $user->can('clientes.modificar');
    }
}
