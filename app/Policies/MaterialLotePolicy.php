<?php

namespace App\Policies;

use App\Models\MaterialLote;
use App\Models\User;

class MaterialLotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('materiales.ver');
    }

    public function view(User $user, MaterialLote $lote): bool
    {
        return $user->can('materiales.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('stock.entrada');
    }

    public function update(User $user, MaterialLote $lote): bool
    {
        return $user->can('stock.ajustar');
    }

    public function delete(User $user, MaterialLote $lote): bool
    {
        return $user->can('stock.ajustar');
    }

    public function restore(User $user, MaterialLote $lote): bool
    {
        return $user->can('stock.ajustar');
    }
}
