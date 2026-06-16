<?php

namespace App\Observers;

use App\Models\TarifaCliente;
use App\Models\TarifaHistorial;
use Illuminate\Support\Facades\Auth;

/**
 * Observer de tarifas al cliente.
 *
 * Auditoría: cada cambio de `importe` (incluida la primera vez) deja una
 * fila en `tarifas_historial` con tipo='cliente'.
 *
 * No bloquea nada: solo registra. La integridad la asegura la tabla con
 * UNIQUE compuesto.
 */
class TarifaClienteObserver
{
    public function created(TarifaCliente $tarifa): void
    {
        TarifaHistorial::create([
            'tipo' => TarifaHistorial::TIPO_CLIENTE,
            'referencia_id' => $tarifa->id,
            'atributo_id' => $tarifa->atributo_id,
            'importe_anterior' => 0,
            'importe_nuevo' => $tarifa->importe,
            'cambiado_por' => Auth::id(),
        ]);
    }

    public function updated(TarifaCliente $tarifa): void
    {
        if (! $tarifa->isDirty('importe')) {
            return;
        }

        TarifaHistorial::create([
            'tipo' => TarifaHistorial::TIPO_CLIENTE,
            'referencia_id' => $tarifa->id,
            'atributo_id' => $tarifa->atributo_id,
            'importe_anterior' => (float) $tarifa->getOriginal('importe'),
            'importe_nuevo' => (float) $tarifa->importe,
            'cambiado_por' => Auth::id(),
        ]);
    }
}
