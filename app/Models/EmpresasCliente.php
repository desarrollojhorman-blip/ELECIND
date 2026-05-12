<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresasCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'nombre_comercial',
        'cif',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'telefono',
        'email',
        'correo_notificaciones',
        'activo',
        'observaciones',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_cliente_id');
    }
}
