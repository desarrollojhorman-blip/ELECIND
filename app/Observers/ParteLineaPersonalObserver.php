<?php

namespace App\Observers;

use App\Models\AtributoHora;
use App\Models\Parte;
use App\Models\ParteLineaPersonal;
use App\Models\TarifaCliente;
use App\Models\User;

/**
 * Snapshot del trabajador y de la tarifa cliente en cada línea del parte.
 *
 * Reglas:
 *  - Cuando `trabajador_id` es dirty → se (re)escriben los 6 snapshots de
 *    trabajador (nombre + tasas) y las 2 tarifas cliente (tarifa_hora,
 *    tarifa_extra) consultadas en tarifas_cliente según el tipo de jornada
 *    del parte y el tipo de proyecto.
 *  - Cuando `horas`, `horas_extra`, `tarifa_hora_snapshot`,
 *    `tarifa_extra_snapshot`, `trabajador_tasa_hora_snapshot` o
 *    `trabajador_tasa_extra_snapshot` son dirty → se recalcula
 *    `facturacion_snapshot` y `coste_snapshot`.
 *
 * Los snapshots de tarifa y tasa se pueden editar manualmente después; esa
 * edición NO resetea los valores porque `trabajador_id` no es dirty.
 */
class ParteLineaPersonalObserver
{
    public function saving(ParteLineaPersonal $linea): void
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

    private function fillWorkerSnapshot(ParteLineaPersonal $linea): void
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

    private function fillTarifaSnapshot(ParteLineaPersonal $linea): void
    {
        $parte = Parte::with('proyecto:id,tipo_proyecto_id')->find($linea->parte_id);

        if ($parte === null || $parte->cliente_id === null) {
            $linea->tarifa_hora_snapshot          = 0;
            $linea->tarifa_extra_snapshot         = 0;
            $linea->tarifa_plus_retencion_snapshot = 0;

            return;
        }

        $tipoProyectoId = $parte->proyecto?->tipo_proyecto_id;

        if ($tipoProyectoId === null) {
            $linea->tarifa_hora_snapshot          = 0;
            $linea->tarifa_extra_snapshot         = 0;
            $linea->tarifa_plus_retencion_snapshot = 0;

            return;
        }

        $tipoHora = $parte->tipo_hora ?? 'laboral';

        $mapa = [
            'laboral'       => [AtributoHora::COD_LABOR,     AtributoHora::COD_EX_LAB],
            'laboral_noche' => [AtributoHora::COD_LAB_NOCHE, AtributoHora::COD_EX_LAB_NOC],
            'festivo'       => [AtributoHora::COD_FEST,      AtributoHora::COD_EX_FES],
            'festivo_noche' => [AtributoHora::COD_FEST_NOCT, AtributoHora::COD_EX_FES_NOCT],
        ];

        [$codNormal, $codExtra] = $mapa[$tipoHora] ?? $mapa['laboral'];

        $atributos = AtributoHora::whereIn('codigo', [$codNormal, $codExtra, AtributoHora::COD_PLUS_RETEN])
            ->pluck('id', 'codigo');

        $idNormal    = $atributos[$codNormal]              ?? null;
        $idExtra     = $atributos[$codExtra]               ?? null;
        $idPlusReten = $atributos[AtributoHora::COD_PLUS_RETEN] ?? null;

        $tarifas = TarifaCliente::where('cliente_id', $parte->cliente_id)
            ->where('tipo_proyecto_id', $tipoProyectoId)
            ->whereIn('atributo_id', array_values(array_filter([$idNormal, $idExtra, $idPlusReten])))
            ->pluck('importe', 'atributo_id');

        $linea->tarifa_hora_snapshot           = $idNormal    ? (float) ($tarifas[$idNormal]    ?? 0) : 0;
        $linea->tarifa_extra_snapshot          = $idExtra     ? (float) ($tarifas[$idExtra]     ?? 0) : 0;
        $linea->tarifa_plus_retencion_snapshot = $idPlusReten ? (float) ($tarifas[$idPlusReten] ?? 0) : 0;
    }

    private function recalcularTotales(ParteLineaPersonal $linea): void
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
