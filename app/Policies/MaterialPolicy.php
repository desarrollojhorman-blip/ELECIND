<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('materiales.ver');
    }

    public function view(User $user, Material $material): bool
    {
        return $user->can('materiales.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('materiales.crear');
    }

    public function update(User $user, Material $material): bool
    {
        return $user->can('materiales.modificar');
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->can('materiales.eliminar');
    }

    public function restore(User $user, Material $material): bool
    {
        return $user->can('materiales.modificar');
    }
}
