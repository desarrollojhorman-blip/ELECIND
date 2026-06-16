<?php

namespace App\Observers;

use App\Models\AtributoHora;
use App\Models\Parte;
use App\Models\ParteLineaPersonal;
use App\Models\TarifaCliente;
use App\Models\User;

/**
 * Snapshots de líneas de personal del parte.
 *
 * Patrón "isDirty": cada bloque de snapshots se (re)escribe solo cuando su FK
 * cambia. Los snapshots económicos (tarifa, tasa, facturación, coste)
 * dependen de cantidad + atributo + trabajador + cliente del parte, así que
 * se recalculan si cualquiera de esos cambia.
 *
 * Nota: la tarifa €/h del cliente se busca en `tarifas_cliente` con
 * (cliente_id_snapshot del parte, tipo_proyecto_id_snapshot del parte,
 * atributo_id). Si no hay fila → tarifa 0.
 */
class ParteLineaPersonalObserver
{
    public function saving(ParteLineaPersonal $linea): void
    {
        if ($linea->isDirty('user_id')) {
            $this->snapshotTrabajador($linea);
        }

        if ($linea->isDirty('atributo_id')) {
            $this->snapshotAtributo($linea);
        }

        // Recalcular snapshots económicos si cambió cualquier insumo:
        // cantidad, atributo o trabajador. Si nada cambió, no se recalcula.
        if ($linea->isDirty('cantidad') || $linea->isDirty('atributo_id') || $linea->isDirty('user_id')) {
            $this->recalcularSnapshotsEconomicos($linea);
        }
    }

    private function snapshotTrabajador(ParteLineaPersonal $linea): void
    {
        if ($linea->user_id === null) {
            $linea->trabajador_nombre_snapshot = null;
            $linea->trabajador_apellidos_snapshot = null;

            return;
        }
        $user = User::withTrashed()->find($linea->user_id);
        if ($user === null) {
            return;
        }
        $linea->trabajador_nombre_snapshot = $user->nombre;
        $linea->trabajador_apellidos_snapshot = $user->apellidos;
    }

    private function snapshotAtributo(ParteLineaPersonal $linea): void
    {
        if ($linea->atributo_id === null) {
            $linea->atributo_codigo_snapshot = null;
            $linea->atributo_nombre_snapshot = null;

            return;
        }
        $attr = AtributoHora::find($linea->atributo_id);
        if ($attr === null) {
            return;
        }
        $linea->atributo_codigo_snapshot = $attr->codigo;
        $linea->atributo_nombre_snapshot = $attr->nombre_corto;
    }

    /**
     * tarifa_snapshot   = tarifa del cliente para (tipo_proyecto, atributo)
     * tasa_snapshot     = tasa del trabajador para el atributo (mapeo_tasa)
     * facturacion       = cantidad × tarifa
     * coste             = cantidad × tasa
     */
    private function recalcularSnapshotsEconomicos(ParteLineaPersonal $linea): void
    {
        $cantidad = (float) $linea->cantidad;
        $tarifa = $this->buscarTarifa($linea);
        $tasa = $this->buscarTasa($linea);

        $linea->tarifa_snapshot = $tarifa;
        $linea->tasa_snapshot = $tasa;
        $linea->facturacion_snapshot = round($cantidad * $tarifa, 2);
        $linea->coste_snapshot = round($cantidad * $tasa, 2);
    }

    /**
     * Tarifa €/h del cliente para esta línea. La buscamos a través del parte:
     * cliente_id_snapshot × tipo_proyecto_id_snapshot × atributo_id.
     */
    private function buscarTarifa(ParteLineaPersonal $linea): float
    {
        if ($linea->atributo_id === null || $linea->parte_id === null) {
            return 0.0;
        }

        $parte = Parte::withTrashed()->find($linea->parte_id);
        if ($parte === null
            || $parte->cliente_id_snapshot === null
            || $parte->tipo_proyecto_id_snapshot === null) {
            return 0.0;
        }

        return (float) TarifaCliente::query()
            ->where('cliente_id', $parte->cliente_id_snapshot)
            ->where('tipo_proyecto_id', $parte->tipo_proyecto_id_snapshot)
            ->where('atributo_id', $linea->atributo_id)
            ->value('importe') ?? 0.0;
    }

    /**
     * Tasa €/h del trabajador para este atributo, según el mapeo del catálogo.
     * Si el atributo es plus (mapeo_tasa NULL) la tasa del trabajador es 0.
     */
    private function buscarTasa(ParteLineaPersonal $linea): float
    {
        if ($linea->user_id === null || $linea->atributo_id === null) {
            return 0.0;
        }

        $atributo = AtributoHora::find($linea->atributo_id);
        if ($atributo === null || $atributo->mapeo_tasa === null) {
            return 0.0;
        }

        $user = User::withTrashed()->find($linea->user_id);
        if ($user === null) {
            return 0.0;
        }

        $campo = $atributo->mapeo_tasa;

        return (float) ($user->{$campo} ?? 0);
    }
}
