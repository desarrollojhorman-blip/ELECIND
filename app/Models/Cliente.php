<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo_cliente',
        'nombre',
        'nombre_comercial',
        'cif',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'telefono',
        'email',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
