<?php

namespace App\Policies;

use App\Models\Concepto;
use App\Models\User;

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

    public function delete(User $user, Concepto $concepto): bool
    {
        return $user->can('conceptos.eliminar');
    }

    public function restore(User $user, Concepto $concepto): bool
    {
        return $user->can('conceptos.modificar');
    }
}
