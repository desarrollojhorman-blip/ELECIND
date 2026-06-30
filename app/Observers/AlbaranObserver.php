<?php

namespace App\Observers;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\User;

/**
 * Snapshot de las relaciones en la cabecera del albarán.
 *
 * Regla única: cada bloque de snapshots se (re)escribe SOLO cuando su FK
 * correspondiente está `dirty` (es decir, al crear el albarán o al cambiarla
 * por otra). Si la FK no ha cambiado, el snapshot no se toca — esto permite
 * al admin editar otros campos (observaciones, fecha…) sin que se le
 * sobreescriban los snapshots históricos.
 *
 * Nullables: `proyecto_id`, `concepto_id`, `responsable_id`. Si se ponen a
 * null, los respectivos snapshots también se limpian.
 *
 * Obligatorios: `cliente_id`, `creado_por`. Siempre tendrán snapshot.
 */
class AlbaranObserver
{
    /**
     * Al borrar DEFINITIVAMENTE un albarán:
     *  - Reabre su parte de origen (el parte es la base y vuelve a ser editable).
     *  - Devuelve el stock de los materiales borrando las líneas vía Eloquent
     *    (el cascade de BD no dispara `AlbaranLineaMaterialObserver`).
     *
     * Solo se ejecuta en `forceDelete()` — el albarán no tiene papelera.
     */
    public function deleting(Albaran $albaran): void
    {
        if (! $albaran->isForceDeleting()) {
            return;
        }

        Parte::where('albaran_id', $albaran->id)->update([
            'albaran_id' => null,
            'estado'     => Parte::ESTADO_ABIERTO,
        ]);

        $albaran->lineasMaterial()->each(fn (AlbaranLineaMaterial $linea) => $linea->delete());
    }

    public function saving(Albaran $albaran): void
    {
        if ($albaran->isDirty('cliente_id')) {
            $this->snapshotCliente($albaran);
        }

        if ($albaran->isDirty('proyecto_id')) {
            $this->snapshotProyecto($albaran);
        }

        if ($albaran->isDirty('concepto_id')) {
            $this->snapshotConcepto($albaran);
        }

        if ($albaran->isDirty('creado_por')) {
            $this->snapshotCreador($albaran);
        }

        if ($albaran->isDirty('responsable_id')) {
            $this->snapshotResponsable($albaran);
        }
    }

    private function snapshotCliente(Albaran $albaran): void
    {
        $cliente = Cliente::withTrashed()->find($albaran->cliente_id);
        if ($cliente === null) {
            return;
        }
        $albaran->cliente_codigo_snapshot = $cliente->codigo_cliente;
        $albaran->cliente_nombre_snapshot = $cliente->nombre;
        $albaran->cliente_cif_snapshot = $cliente->cif;
    }

    private function snapshotProyecto(Albaran $albaran): void
    {
        if ($albaran->proyecto_id === null) {
            $albaran->proyecto_codigo_snapshot = null;
            $albaran->proyecto_nombre_snapshot = null;

            return;
        }
        $proyecto = Proyecto::withTrashed()->find($albaran->proyecto_id);
        if ($proyecto === null) {
            return;
        }
        $albaran->proyecto_codigo_snapshot = $proyecto->codigo;
        $albaran->proyecto_nombre_snapshot = $proyecto->nombre;
    }

    private function snapshotConcepto(Albaran $albaran): void
    {
        if ($albaran->concepto_id === null) {
            $albaran->concepto_nombre_snapshot = null;

            return;
        }
        $concepto = Concepto::withTrashed()->find($albaran->concepto_id);
        if ($concepto === null) {
            return;
        }
        $albaran->concepto_nombre_snapshot = $concepto->nombre;
    }

    private function snapshotCreador(Albaran $albaran): void
    {
        $user = User::withTrashed()->find($albaran->creado_por);
        if ($user === null) {
            return;
        }
        $albaran->creador_username_snapshot = $user->username;
        $albaran->creador_nombre_snapshot = $user->nombre;
        $albaran->creador_apellidos_snapshot = $user->apellidos;
        $albaran->creador_numero_empleado_snapshot = $user->numero_empleado;
    }

    private function snapshotResponsable(Albaran $albaran): void
    {
        if ($albaran->responsable_id === null) {
            $albaran->responsable_username_snapshot = null;
            $albaran->responsable_nombre_snapshot = null;
            $albaran->responsable_apellidos_snapshot = null;
            $albaran->responsable_numero_empleado_snapshot = null;

            return;
        }
        $user = User::withTrashed()->find($albaran->responsable_id);
        if ($user === null) {
            return;
        }
        $albaran->responsable_username_snapshot = $user->username;
        $albaran->responsable_nombre_snapshot = $user->nombre;
        $albaran->responsable_apellidos_snapshot = $user->apellidos;
        $albaran->responsable_numero_empleado_snapshot = $user->numero_empleado;
    }
}
