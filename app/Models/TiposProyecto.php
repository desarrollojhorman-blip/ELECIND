<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TiposProyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'color',
        'descripcion',
        'activo',
    ];

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class, 'tipo_proyecto_id');
    }
}
