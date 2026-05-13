<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string $ambito
 * @property string|null $descripcion
 * @property string|null $categoria
 */
class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'ambito',
        'descripcion',
        'categoria',
    ];
}
