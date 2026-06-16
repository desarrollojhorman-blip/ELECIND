<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de personal de un parte.
 *
 * Una fila por (trabajador × atributo). Un mismo trabajador puede tener
 * varias líneas en el mismo parte si imputa más de un atributo en el día.
 *
 * Snapshots económicos (tarifa, tasa, facturación, coste) los mantiene
 * ParteLineaPersonalObserver al asignar/cambiar las FK.
 *
 * @property int $id
 * @property int $parte_id
 * @property int $user_id
 * @property int $atributo_id
 * @property float $cantidad
 * @property string|null $motivo_ajuste
 * @property string|null $trabajador_nombre_snapshot
 * @property string|null $trabajador_apellidos_snapshot
 * @property string|null $atributo_codigo_snapshot
 * @property string|null $atributo_nombre_snapshot
 * @property float $tarifa_snapshot
 * @property float $tasa_snapshot
 * @property float $facturacion_snapshot
 * @property float $coste_snapshot
 */
class ParteLineaPersonal extends Model
{
    protected $table = 'partes_lineas_personal';

    protected $fillable = [
        'parte_id',
        'user_id',
        'atributo_id',
        'cantidad',
        'motivo_ajuste',
        // Snapshots
        'trabajador_nombre_snapshot',
        'trabajador_apellidos_snapshot',
        'atributo_codigo_snapshot',
        'atributo_nombre_snapshot',
        'tarifa_snapshot',
        'tasa_snapshot',
        'facturacion_snapshot',
        'coste_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'tarifa_snapshot' => 'decimal:4',
            'tasa_snapshot' => 'decimal:3',
            'facturacion_snapshot' => 'decimal:2',
            'coste_snapshot' => 'decimal:2',
        ];
    }

    /* ── Relaciones ─────────────────────────────────────────── */

    public function parte(): BelongsTo
    {
        return $this->belongsTo(Parte::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function atributo(): BelongsTo
    {
        return $this->belongsTo(AtributoHora::class, 'atributo_id');
    }

    /* ── Accesors ──────────────────────────────────────────── */

    /** Margen de la línea (facturación – coste). */
    public function margen(): float
    {
        return (float) $this->facturacion_snapshot - (float) $this->coste_snapshot;
    }
}
