<?php

namespace App\Policies;

use App\Models\FamiliaMaterial;
use App\Models\User;

class FamiliaMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('materiales.familias.ver');
    }

    public function view(User $user, FamiliaMaterial $familia): bool
    {
        return $user->can('materiales.familias.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('materiales.familias.crear');
    }

    public function update(User $user, FamiliaMaterial $familia): bool
    {
        return $user->can('materiales.familias.modificar');
    }

    public function delete(User $user, FamiliaMaterial $familia): bool
    {
        return $user->can('materiales.familias.eliminar');
    }

}
