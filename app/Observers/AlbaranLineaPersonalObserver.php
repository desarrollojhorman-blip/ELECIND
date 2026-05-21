<?php

namespace App\Observers;

use App\Models\AlbaranLineaPersonal;
use App\Models\User;

/**
 * Snapshot del trabajador en cada línea de personal.
 *
 * Regla única: solo se (re)escribe el snapshot cuando cambia la FK
 * `trabajador_id` (al crear la línea o al reasignarla a otro trabajador).
 *
 * Al editar otros campos (horas, horas_extra, o los propios campos snapshot
 * a mano), `trabajador_id` no es `dirty` → el Observer NO toca el snapshot.
 * Esto permite que el admin pueda ajustar las tasas manualmente en la línea
 * sin que se le sobreescriban en el siguiente save.
 */
class AlbaranLineaPersonalObserver
{
    public function saving(AlbaranLineaPersonal $linea): void
    {
        if (! $linea->isDirty('trabajador_id')) {
            return;
        }

        // trabajador_id es obligatorio (la línea no existe sin trabajador).
        $user = User::withTrashed()->find($linea->trabajador_id);
        if ($user === null) {
            return; // Defensa: si la FK apunta a algo que no existe, no machacamos.
        }

        $linea->trabajador_nombre_snapshot = $user->nombre;
        $linea->trabajador_apellidos_snapshot = $user->apellidos;
        $linea->trabajador_numero_empleado_snapshot = $user->numero_empleado;
        $linea->trabajador_tasa_hora_snapshot = $user->tasa_hora;
        $linea->trabajador_tasa_extra_snapshot = $user->tasa_extra;
        $linea->trabajador_tasa_festivo_snapshot = $user->tasa_festivo;
    }
}
