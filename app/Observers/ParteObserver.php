<?php

namespace App\Observers;

use App\Models\Parte;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Snapshot de las relaciones en la cabecera del parte.
 *
 * Mismo patrón que AlbaranObserver: cada bloque de snapshots se (re)escribe
 * SOLO cuando su FK está `dirty` (al crear o al cambiarla).
 *
 * Además:
 *  - `creating`: genera el código PT-YYYY-NNNN secuencial por año.
 *  - `creating`: rellena `es_albaran` desde
 *    `tipo_proyecto.genera_albaran_por_defecto` si no se especificó.
 */
class ParteObserver
{
    public function creating(Parte $parte): void
    {
        // Autocódigo PT-YYYY-NNNN.
        if (empty($parte->codigo)) {
            $parte->codigo = $this->siguienteCodigo();
        }

        // Si `es_albaran` no se ha especificado activamente, deducirlo del
        // tipo_proyecto del proyecto seleccionado. El operario puede haberlo
        // marcado explícitamente; en ese caso (atributo "dirty"), respetamos.
        if (! $parte->isDirty('es_albaran') && $parte->proyecto_id) {
            $proyecto = Proyecto::find($parte->proyecto_id);
            $tipo = $proyecto?->tipo_proyecto_id
                ? TiposProyecto::find($proyecto->tipo_proyecto_id)
                : null;
            if ($tipo !== null) {
                $parte->es_albaran = (bool) $tipo->genera_albaran_por_defecto;
            }
        }
    }

    public function saving(Parte $parte): void
    {
        if ($parte->isDirty('user_id')) {
            $this->snapshotOperario($parte);
        }

        if ($parte->isDirty('proyecto_id')) {
            $this->snapshotProyectoYCliente($parte);
        }
    }

    /**
     * Próximo código PT-YYYY-NNNN, padded a 4 dígitos, por año del campo
     * `fecha` (si está) o del año actual.
     */
    private function siguienteCodigo(): string
    {
        // Año tomado del campo fecha si está; si no, del año actual del servidor.
        $anio = Carbon::now()->year;

        $ultimo = Parte::query()
            ->where('codigo', 'like', 'PT-'.$anio.'-%')
            ->orderByDesc('codigo')
            ->value('codigo');

        $siguiente = 1;
        if ($ultimo !== null) {
            // PT-YYYY-NNNN → NNNN
            $partes = explode('-', $ultimo);
            $siguiente = ((int) end($partes)) + 1;
        }

        return sprintf('PT-%d-%04d', $anio, $siguiente);
    }

    private function snapshotOperario(Parte $parte): void
    {
        if ($parte->user_id === null) {
            $parte->operario_nombre_snapshot = null;

            return;
        }
        $user = User::withTrashed()->find($parte->user_id);
        if ($user === null) {
            return;
        }
        $parte->operario_nombre_snapshot = trim($user->apellidos.' '.$user->nombre) ?: $user->username;
    }

    private function snapshotProyectoYCliente(Parte $parte): void
    {
        if ($parte->proyecto_id === null) {
            $parte->proyecto_codigo_snapshot = null;
            $parte->proyecto_nombre_snapshot = null;
            $parte->cliente_id_snapshot = null;
            $parte->cliente_nombre_snapshot = null;
            $parte->tipo_proyecto_id_snapshot = null;
            $parte->tipo_proyecto_nombre_snapshot = null;

            return;
        }

        $proyecto = Proyecto::withTrashed()
            ->with(['cliente:id,nombre', 'tipoProyecto:id,nombre'])
            ->find($parte->proyecto_id);

        if ($proyecto === null) {
            return;
        }

        $parte->proyecto_codigo_snapshot = $proyecto->codigo;
        $parte->proyecto_nombre_snapshot = $proyecto->nombre;
        $parte->cliente_id_snapshot = $proyecto->cliente_id;
        $parte->cliente_nombre_snapshot = $proyecto->cliente?->nombre;
        $parte->tipo_proyecto_id_snapshot = $proyecto->tipo_proyecto_id;
        $parte->tipo_proyecto_nombre_snapshot = $proyecto->tipoProyecto?->nombre;
    }
}
