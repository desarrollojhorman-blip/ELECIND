<?php

namespace App\Observers;

use App\Models\AlbaranLineaMaterial;
use App\Models\MaterialLote;
use Illuminate\Support\Facades\DB;

class AlbaranLineaMaterialObserver
{
    /**
     * Al crear una línea de material, descontamos la cantidad del lote.
     */
    public function created(AlbaranLineaMaterial $linea): void
    {
        $this->ajustarStock($linea->material_lote_id, -(float) $linea->cantidad);
    }

    /**
     * Al actualizar una línea de material, ajustamos la diferencia (sumando lo que
     * había antes y restando lo nuevo). Si cambió de lote, devolvemos al lote
     * anterior y descontamos del nuevo.
     */
    public function updated(AlbaranLineaMaterial $linea): void
    {
        $cantidadVieja = (float) $linea->getOriginal('cantidad');
        $loteViejoId = (int) $linea->getOriginal('material_lote_id');
        $cantidadNueva = (float) $linea->cantidad;
        $loteNuevoId = (int) $linea->material_lote_id;

        if ($loteViejoId === $loteNuevoId) {
            // Mismo lote: ajustar el diff (positivo = devolver al lote, negativo = restar).
            $diff = $cantidadVieja - $cantidadNueva;
            if ($diff !== 0.0) {
                $this->ajustarStock($loteNuevoId, $diff);
            }

            return;
        }

        // Cambio de lote: devolver al viejo, descontar del nuevo.
        $this->ajustarStock($loteViejoId, $cantidadVieja);
        $this->ajustarStock($loteNuevoId, -$cantidadNueva);
    }

    /**
     * Al eliminar una línea de material, devolvemos la cantidad al lote.
     */
    public function deleted(AlbaranLineaMaterial $linea): void
    {
        $this->ajustarStock($linea->material_lote_id, (float) $linea->cantidad);
    }

    private function ajustarStock(int $loteId, float $delta): void
    {
        if ($delta === 0.0) {
            return;
        }

        DB::transaction(function () use ($loteId, $delta): void {
            /** @var MaterialLote|null $lote */
            $lote = MaterialLote::query()->lockForUpdate()->find($loteId);

            if ($lote === null) {
                return;
            }

            $lote->stock_disponible = (float) $lote->stock_disponible + $delta;
            $lote->save();
        });
    }
}
