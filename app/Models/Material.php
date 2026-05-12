<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'materiales';

    protected $fillable = [
        'codigo',
        'grupo',
        'nombre',
        'descripcion',
        'unidad_medida',
        'stock_minimo',
        'notificar_stock_bajo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'stock_minimo' => 'decimal:2',
            'notificar_stock_bajo' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(MaterialLote::class);
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'material_proyecto')
            ->withPivot('cantidad_prevista')
            ->withTimestamps();
    }
}
