<?php

namespace App\Policies;

use App\Models\NumeroPedido;
use App\Models\User;

class NumeroPedidoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pedidos.ver');
    }

    public function view(User $user, NumeroPedido $pedido): bool
    {
        return $user->can('pedidos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('pedidos.crear');
    }

    public function update(User $user, NumeroPedido $pedido): bool
    {
        return $user->can('pedidos.modificar');
    }

    public function delete(User $user, NumeroPedido $pedido): bool
    {
        return $user->can('pedidos.eliminar');
    }

    public function restore(User $user, NumeroPedido $pedido): bool
    {
        return $user->can('pedidos.modificar');
    }
}
