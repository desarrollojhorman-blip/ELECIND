<?php

namespace App\Observers;

use App\Models\Albaran;
use App\Models\AlbaranLineaPersonal;
use App\Models\AtributoHora;
use App\Models\TarifaCliente;
use App\Models\User;

/**
 * Snapshot del trabajador y de la tarifa cliente en cada línea del albarán.
 *
 * Mismas reglas que ParteLineaPersonalObserver:
 *  - `trabajador_id` dirty → snapshots de trabajador + tarifas cliente.
 *  - Cualquier campo que afecte al cálculo dirty → recalcula
 *    facturacion_snapshot y coste_snapshot.
 */
class AlbaranLineaPersonalObserver
{
    public function saving(AlbaranLineaPersonal $linea): void
    {
        $trabajadorDirty = $linea->isDirty('trabajador_id');

        $needsRecalc = $trabajadorDirty
            || $linea->isDirty('horas')
            || $linea->isDirty('horas_extra')
            || $linea->isDirty('tarifa_hora_snapshot')
            || $linea->isDirty('tarifa_extra_snapshot')
            || $linea->isDirty('trabajador_tasa_hora_snapshot')
            || $linea->isDirty('trabajador_tasa_extra_snapshot');

        if ($trabajadorDirty) {
            $this->fillWorkerSnapshot($linea);
            $this->fillTarifaSnapshot($linea);
        }

        if ($needsRecalc) {
            $this->recalcularTotales($linea);
        }
    }

    private function fillWorkerSnapshot(AlbaranLineaPersonal $linea): void
    {
        $user = User::withTrashed()->find($linea->trabajador_id);
        if ($user === null) {
            return;
        }

        $linea->trabajador_nombre_snapshot                  = $user->nombre;
        $linea->trabajador_apellidos_snapshot               = $user->apellidos;
        $linea->trabajador_numero_empleado_snapshot         = $user->numero_empleado;
        $linea->trabajador_tasa_hora_snapshot               = $user->tasa_hora;
        $linea->trabajador_tasa_extra_snapshot              = $user->tasa_extra;
        $linea->trabajador_tasa_festivo_snapshot            = $user->tasa_festivo;
        $linea->trabajador_tasa_plus_retencion_snapshot     = $user->tasa_plus_reten;
    }

    private function fillTarifaSnapshot(AlbaranLineaPersonal $linea): void
    {
        $albaran = Albaran::with('proyecto:id,tipo_proyecto_id')->find($linea->albaran_id);

        if ($albaran === null || $albaran->cliente_id === null) {
            $linea->tarifa_hora_snapshot           = 0;
            $linea->tarifa_extra_snapshot          = 0;
            $linea->tarifa_plus_retencion_snapshot = 0;

            return;
        }

        $tipoProyectoId = $albaran->proyecto?->tipo_proyecto_id;

        if ($tipoProyectoId === null) {
            $linea->tarifa_hora_snapshot           = 0;
            $linea->tarifa_extra_snapshot          = 0;
            $linea->tarifa_plus_retencion_snapshot = 0;

            return;
        }

        $tipoHora = $albaran->tipo_hora instanceof \App\Enums\TipoHora
            ? $albaran->tipo_hora->value
            : ($albaran->tipo_hora ?? 'laboral');

        $mapa = [
            'laboral'       => [AtributoHora::COD_LABOR,     AtributoHora::COD_EX_LAB],
            'laboral_noche' => [AtributoHora::COD_LAB_NOCHE, AtributoHora::COD_EX_LAB_NOC],
            'festivo'       => [AtributoHora::COD_FEST,      AtributoHora::COD_EX_FES],
            'festivo_noche' => [AtributoHora::COD_FEST_NOCT, AtributoHora::COD_EX_FES_NOCT],
        ];

        [$codNormal, $codExtra] = $mapa[$tipoHora] ?? $mapa['laboral'];

        $atributos = AtributoHora::whereIn('codigo', [$codNormal, $codExtra, AtributoHora::COD_PLUS_RETEN])
            ->pluck('id', 'codigo');

        $idNormal    = $atributos[$codNormal]                   ?? null;
        $idExtra     = $atributos[$codExtra]                    ?? null;
        $idPlusReten = $atributos[AtributoHora::COD_PLUS_RETEN] ?? null;

        $tarifas = TarifaCliente::where('cliente_id', $albaran->cliente_id)
            ->where('tipo_proyecto_id', $tipoProyectoId)
            ->whereIn('atributo_id', array_values(array_filter([$idNormal, $idExtra, $idPlusReten])))
            ->pluck('importe', 'atributo_id');

        $linea->tarifa_hora_snapshot           = $idNormal    ? (float) ($tarifas[$idNormal]    ?? 0) : 0;
        $linea->tarifa_extra_snapshot          = $idExtra     ? (float) ($tarifas[$idExtra]     ?? 0) : 0;
        $linea->tarifa_plus_retencion_snapshot = $idPlusReten ? (float) ($tarifas[$idPlusReten] ?? 0) : 0;
    }

    private function recalcularTotales(AlbaranLineaPersonal $linea): void
    {
        $linea->facturacion_snapshot = round(
            ((float) $linea->horas)       * ((float) $linea->tarifa_hora_snapshot)
            + ((float) $linea->horas_extra) * ((float) $linea->tarifa_extra_snapshot),
            2
        );

        $linea->coste_snapshot = round(
            ((float) $linea->horas)       * ((float) $linea->trabajador_tasa_hora_snapshot)
            + ((float) $linea->horas_extra) * ((float) $linea->trabajador_tasa_extra_snapshot),
            2
        );
    }
}
