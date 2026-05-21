<?php

namespace App\Observers;

use App\Models\AlbaranLineaMaterial;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

/**
 * Observer de líneas de material. Tiene dos responsabilidades:
 *
 *  1) Mantener el stock del material al día (created/updated/deleted).
 *  2) Rellenar el snapshot del material en la línea (saving) — descripción,
 *     unidad, nº pedido, familia, precio coste, precio venta. Misma regla
 *     que el resto de snapshots: solo se (re)escribe si `material_id` cambió.
 *
 * Stock NO se snapshotea (es volátil global) — solo se ajusta.
 */
class AlbaranLineaMaterialObserver
{
    /* ── Snapshot del material (al guardar) ───────────────────────────── */

    public function saving(AlbaranLineaMaterial $linea): void
    {
        if (! $linea->isDirty('material_id')) {
            return;
        }

        // material_id es obligatorio (la línea no existe sin material).
        $material = Material::withTrashed()
            ->with(['numeroPedido:id,numero', 'familia:id,nombre'])
            ->find($linea->material_id);

        if ($material === null) {
            return;
        }

        $linea->material_descripcion_snapshot = $material->descripcion;
        $linea->material_unidad_medida_snapshot = $material->unidad_medida;
        $linea->material_numero_pedido_snapshot = $material->numeroPedido?->numero;
        $linea->material_familia_snapshot = $material->familia?->nombre;
        $linea->material_precio_coste_snapshot = $material->precio_coste;
        $linea->material_precio_venta_snapshot = $material->precio_venta;
    }

    /* ── Ajuste de stock (eventos post-guardado) ──────────────────────── */

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
