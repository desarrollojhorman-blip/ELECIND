<?php

namespace App\Observers;

use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Snapshot de las relaciones en la cabecera del parte.
 *
 * Mismo patrón que AlbaranObserver: cada bloque de snapshots se (re)escribe
 * SOLO cuando su FK correspondiente está `dirty`.
 *
 * Además:
 *   - `creating`: genera el número PT{YY}-{N} secuencial por año (ej. PT26-1).
 */
class ParteObserver
{
    public function creating(Parte $parte): void
    {
        if (empty($parte->numero)) {
            $parte->numero = $this->siguienteNumero();
        }
    }

    public function saving(Parte $parte): void
    {
        if ($parte->isDirty('cliente_id')) {
            $this->snapshotCliente($parte);
        }

        if ($parte->isDirty('proyecto_id')) {
            $this->snapshotProyecto($parte);
        }

        if ($parte->isDirty('concepto_id')) {
            $this->snapshotConcepto($parte);
        }

        if ($parte->isDirty('creado_por')) {
            $this->snapshotCreador($parte);
        }

        if ($parte->isDirty('responsable_id')) {
            $this->snapshotResponsable($parte);
        }
    }

    /**
     * Próximo número PT{YY}-{N} sin ceros de relleno, secuencial por año.
     * Ejemplo: PT26-1, PT26-2, …, PT26-10, PT27-1.
     */
    private function siguienteNumero(): string
    {
        $anio = Carbon::now()->year;
        $anio2 = substr((string) $anio, 2); // "2026" → "26"
        $prefijo = 'PT'.$anio2.'-';

        $ultimo = Parte::query()
            ->withTrashed()
            ->where('numero', 'like', $prefijo.'%')
            ->orderByDesc('id')
            ->value('numero');

        $siguiente = 1;
        if ($ultimo !== null) {
            $siguiente = ((int) substr($ultimo, strlen($prefijo))) + 1;
        }

        return $prefijo.$siguiente;
    }

    private function snapshotCliente(Parte $parte): void
    {
        if ($parte->cliente_id === null) {
            $parte->cliente_codigo_snapshot = null;
            $parte->cliente_nombre_snapshot = null;
            $parte->cliente_cif_snapshot = null;

            return;
        }
        $cliente = Cliente::withTrashed()->find($parte->cliente_id);
        if ($cliente === null) {
            return;
        }
        $parte->cliente_codigo_snapshot = $cliente->codigo_cliente;
        $parte->cliente_nombre_snapshot = $cliente->nombre;
        $parte->cliente_cif_snapshot = $cliente->cif;
    }

    private function snapshotProyecto(Parte $parte): void
    {
        if ($parte->proyecto_id === null) {
            $parte->proyecto_codigo_snapshot = null;
            $parte->proyecto_nombre_snapshot = null;

            return;
        }
        $proyecto = Proyecto::withTrashed()->find($parte->proyecto_id);
        if ($proyecto === null) {
            return;
        }
        $parte->proyecto_codigo_snapshot = $proyecto->codigo;
        $parte->proyecto_nombre_snapshot = $proyecto->nombre;
    }

    private function snapshotConcepto(Parte $parte): void
    {
        if ($parte->concepto_id === null) {
            $parte->concepto_nombre_snapshot = null;

            return;
        }
        $concepto = Concepto::withTrashed()->find($parte->concepto_id);
        if ($concepto === null) {
            return;
        }
        $parte->concepto_nombre_snapshot = $concepto->nombre;
    }

    private function snapshotCreador(Parte $parte): void
    {
        if ($parte->creado_por === null) {
            $parte->creador_username_snapshot = null;
            $parte->creador_nombre_snapshot = null;
            $parte->creador_apellidos_snapshot = null;
            $parte->creador_numero_empleado_snapshot = null;

            return;
        }
        $user = User::withTrashed()->find($parte->creado_por);
        if ($user === null) {
            return;
        }
        $parte->creador_username_snapshot = $user->username;
        $parte->creador_nombre_snapshot = $user->nombre;
        $parte->creador_apellidos_snapshot = $user->apellidos;
        $parte->creador_numero_empleado_snapshot = $user->numero_empleado;
    }

    private function snapshotResponsable(Parte $parte): void
    {
        if ($parte->responsable_id === null) {
            $parte->responsable_username_snapshot = null;
            $parte->responsable_nombre_snapshot = null;
            $parte->responsable_apellidos_snapshot = null;
            $parte->responsable_numero_empleado_snapshot = null;

            return;
        }
        $user = User::withTrashed()->find($parte->responsable_id);
        if ($user === null) {
            return;
        }
        $parte->responsable_username_snapshot = $user->username;
        $parte->responsable_nombre_snapshot = $user->nombre;
        $parte->responsable_apellidos_snapshot = $user->apellidos;
        $parte->responsable_numero_empleado_snapshot = $user->numero_empleado;
    }
}
