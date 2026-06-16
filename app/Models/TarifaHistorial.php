<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historial unificado de cambios de tarifas (cliente y trabajador).
 *
 * `tipo` distingue qué tabla origen tiene el cambio:
 *   - 'cliente'     → referencia_id es tarifas_cliente.id
 *   - 'trabajador'  → referencia_id es users.id
 *
 * Los Observers escriben aquí automáticamente. La pantalla "Tarifas → Historial"
 * lee con filtros opcionales por tipo.
 */
class TarifaHistorial extends Model
{
    protected $table = 'tarifas_historial';

    /** Solo se crea, no se actualiza. */
    public const UPDATED_AT = null;

    public const TIPO_CLIENTE = 'cliente';

    public const TIPO_TRABAJADOR = 'trabajador';

    protected $fillable = [
        'tipo',
        'referencia_id',
        'atributo_id',
        'importe_anterior',
        'importe_nuevo',
        'cambiado_por',
        'motivo',
    ];

    protected $casts = [
        'importe_anterior' => 'decimal:4',
        'importe_nuevo' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    public function atributo(): BelongsTo
    {
        return $this->belongsTo(AtributoHora::class, 'atributo_id');
    }

    public function cambiadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cambiado_por');
    }

    /* ── Scopes ──────────────────────────────────────────────────────── */

    public function scopeClientes(Builder $q): Builder
    {
        return $q->where('tipo', self::TIPO_CLIENTE);
    }

    public function scopeTrabajadores(Builder $q): Builder
    {
        return $q->where('tipo', self::TIPO_TRABAJADOR);
    }
}
