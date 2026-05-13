<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property int $nivel
 * @property string $acceso
 * @property bool $es_sistema
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'nivel',
        'acceso',
        'es_sistema',
    ];

    protected function casts(): array
    {
        return [
            'nivel' => 'integer',
            'es_sistema' => 'boolean',
        ];
    }
}
