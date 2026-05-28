<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $id
 * @property string $name
 * @property ?string $etiqueta
 * @property string $guard_name
 * @property int $nivel
 * @property string $acceso
 * @property bool $es_sistema
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'etiqueta',
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

    /** Nombre legible para la interfaz (etiqueta o, en su defecto, el name "humanizado"). */
    public function nombreVisible(): string
    {
        return $this->etiqueta ?: ucfirst(str_replace('_', ' ', $this->name));
    }
}
