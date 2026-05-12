<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_cliente_id',
        'tipo_proyecto_id',
        'nombre',
        'codigo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'responsable_principal_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function empresaCliente(): BelongsTo
    {
        return $this->belongsTo(EmpresasCliente::class);
    }

    public function tipoProyecto(): BelongsTo
    {
        return $this->belongsTo(TiposProyecto::class, 'tipo_proyecto_id');
    }

    public function responsablePrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_principal_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proyecto_usuario')
            ->withPivot('rol_en_proyecto')
            ->withTimestamps();
    }

    public function conceptos(): BelongsToMany
    {
        return $this->belongsToMany(Concepto::class, 'proyecto_concepto')->withTimestamps();
    }

    public function materiales(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_proyecto')
            ->withPivot('cantidad_prevista')
            ->withTimestamps();
    }
}
