<?php

namespace App\Observers;

use App\Models\AlbaranLineaMaterial;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

class AlbaranLineaMaterialObserver
{
    /**
     * Al crear una línea de material, descontamos la cantidad del stock del material.
     */
    public function created(AlbaranLineaMaterial $linea): void
    {
        $this->ajustarStock($linea->material_id, -(float) $linea->cantidad);
    }

    /**
     * Al actualizar una línea de material, ajustamos la diferencia.
     * Si cambió de material, devolvemos al anterior y descontamos del nuevo.
     */
    public function updated(AlbaranLineaMaterial $linea): void
    {
        $cantidadVieja = (float) $linea->getOriginal('cantidad');
        $materialViejoId = (int) $linea->getOriginal('material_id');
        $cantidadNueva = (float) $linea->cantidad;
        $materialNuevoId = (int) $linea->material_id;

        if ($materialViejoId === $materialNuevoId) {
            $diff = $cantidadVieja - $cantidadNueva;
            if ($diff !== 0.0) {
                $this->ajustarStock($materialNuevoId, $diff);
            }

            return;
        }

        // Cambio de material: devolver al viejo, descontar del nuevo.
        $this->ajustarStock($materialViejoId, $cantidadVieja);
        $this->ajustarStock($materialNuevoId, -$cantidadNueva);
    }

    /**
     * Al eliminar una línea de material, devolvemos la cantidad al stock del material.
     */
    public function deleted(AlbaranLineaMaterial $linea): void
    {
        $this->ajustarStock($linea->material_id, (float) $linea->cantidad);
    }

    private function ajustarStock(int $materialId, float $delta): void
    {
        if ($delta === 0.0) {
            return;
        }

        DB::transaction(function () use ($materialId, $delta): void {
            /** @var Material|null $material */
            $material = Material::query()->lockForUpdate()->find($materialId);

            if ($material === null) {
                return;
            }

            $material->stock = (float) $material->stock + $delta;
            $material->save();
        });
    }
}
