<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Borrador extends Model
{
    use SoftDeletes;

    protected $table = 'borradores';

    protected $fillable = [
        'numero_borrador',
        'proyecto_id',
        'proyecto_texto',
        'cliente_id',
        'cliente_texto',
        'concepto_id',
        'concepto_texto',
        'responsable_id',
        'fecha',
        'tipo_hora',
        'estado',
        'observaciones',
        'convertido_a_albaran_id',
        'creado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Concepto::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function albaranConvertido(): BelongsTo
    {
        return $this->belongsTo(Albaran::class, 'convertido_a_albaran_id');
    }

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(BorradorLineaPersonal::class);
    }

    public function lineasMaterial(): HasMany
    {
        return $this->hasMany(BorradorLineaMaterial::class);
    }

    public function estaConvertido(): bool
    {
        return $this->estado === 'convertido';
    }

    /** Nombre a mostrar del proyecto (FK o texto libre). */
    public function proyectoNombre(): string
    {
        return $this->proyecto?->nombre ?? $this->proyecto_texto ?? '—';
    }

    /** Nombre a mostrar del cliente (FK o texto libre). */
    public function clienteNombre(): string
    {
        return $this->cliente?->nombre ?? $this->cliente_texto ?? '—';
    }

    /** Nombre a mostrar del concepto (FK o texto libre). */
    public function conceptoNombre(): string
    {
        return $this->concepto?->nombre ?? $this->concepto_texto ?? '—';
    }
}
