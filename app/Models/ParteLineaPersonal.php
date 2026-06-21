<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de personal de un parte.
 *
 * Esquema idéntico a AlbaranLineaPersonal: una fila por (parte, trabajador)
 * con `horas` (normales) y `horas_extra`. El tipo de jornada se hereda de
 * la cabecera del parte (campo `tipo_hora`).
 *
 * Los snapshots del trabajador (nombre, apellidos, nº empleado y 3 tasas)
 * los mantiene ParteLineaPersonalObserver al asignar/cambiar `trabajador_id`.
 *
 * @property int $id
 * @property int $parte_id
 * @property int $trabajador_id
 * @property string $horas
 * @property string $horas_extra
 * @property string|null $trabajador_nombre_snapshot
 * @property string|null $trabajador_apellidos_snapshot
 * @property string|null $trabajador_numero_empleado_snapshot
 * @property string|null $trabajador_tasa_hora_snapshot
 * @property string|null $trabajador_tasa_extra_snapshot
 * @property string|null $trabajador_tasa_festivo_snapshot
 * @property string|null $tarifa_hora_snapshot
 * @property string|null $tarifa_extra_snapshot
 * @property string|null $tarifa_plus_retencion_snapshot
 * @property string|null $trabajador_tasa_plus_retencion_snapshot
 * @property string|null $facturacion_snapshot
 * @property string|null $coste_snapshot
 */
class ParteLineaPersonal extends Model
{
    protected $table = 'partes_lineas_personal';

    protected $fillable = [
        'parte_id',
        'trabajador_id',
        'horas',
        'horas_extra',
        'trabajador_nombre_snapshot',
        'trabajador_apellidos_snapshot',
        'trabajador_numero_empleado_snapshot',
        'trabajador_tasa_hora_snapshot',
        'trabajador_tasa_extra_snapshot',
        'trabajador_tasa_festivo_snapshot',
        'tarifa_hora_snapshot',
        'tarifa_extra_snapshot',
        'tarifa_plus_retencion_snapshot',
        'trabajador_tasa_plus_retencion_snapshot',
        'facturacion_snapshot',
        'coste_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'horas' => 'decimal:2',
            'horas_extra' => 'decimal:2',
            'trabajador_tasa_hora_snapshot' => 'decimal:3',
            'trabajador_tasa_extra_snapshot' => 'decimal:3',
            'trabajador_tasa_festivo_snapshot' => 'decimal:3',
            'tarifa_hora_snapshot' => 'decimal:4',
            'tarifa_extra_snapshot' => 'decimal:4',
            'tarifa_plus_retencion_snapshot' => 'decimal:4',
            'trabajador_tasa_plus_retencion_snapshot' => 'decimal:3',
            'facturacion_snapshot' => 'decimal:2',
            'coste_snapshot' => 'decimal:2',
        ];
    }

    public function parte(): BelongsTo
    {
        return $this->belongsTo(Parte::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }
}
