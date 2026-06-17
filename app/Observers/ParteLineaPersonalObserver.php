<?php

namespace App\Observers;

use App\Models\ParteLineaPersonal;
use App\Models\User;

/**
 * Snapshot del trabajador en cada línea de personal del parte.
 *
 * Mismo comportamiento que AlbaranLineaPersonalObserver: el snapshot solo
 * se (re)escribe cuando `trabajador_id` está dirty (al crear la línea o
 * cambiar el trabajador). Tocar otros campos NO resetea el snapshot.
 */
class ParteLineaPersonalObserver
{
    public function saving(ParteLineaPersonal $linea): void
    {
        if (! $linea->isDirty('trabajador_id')) {
            return;
        }

        $user = User::withTrashed()->find($linea->trabajador_id);
        if ($user === null) {
            return;
        }

        $linea->trabajador_nombre_snapshot = $user->nombre;
        $linea->trabajador_apellidos_snapshot = $user->apellidos;
        $linea->trabajador_numero_empleado_snapshot = $user->numero_empleado;
        $linea->trabajador_tasa_hora_snapshot = $user->tasa_hora;
        $linea->trabajador_tasa_extra_snapshot = $user->tasa_extra;
        $linea->trabajador_tasa_festivo_snapshot = $user->tasa_festivo;
    }
}
