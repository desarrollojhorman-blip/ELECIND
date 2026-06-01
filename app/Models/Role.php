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
 * @property bool $solo_clientes_asignados
 * @property bool $es_externo
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
        'solo_clientes_asignados',
        'es_externo',
    ];

    protected function casts(): array
    {
        return [
            'nivel' => 'integer',
            'es_sistema' => 'boolean',
            'solo_clientes_asignados' => 'boolean',
            'es_externo' => 'boolean',
        ];
    }

    /** Nombre legible para la interfaz (etiqueta o, en su defecto, el name "humanizado"). */
    public function nombreVisible(): string
    {
        return $this->etiqueta ?: ucfirst(str_replace('_', ' ', $this->name));
    }
}
