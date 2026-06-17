<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\ParteLineaMaterial;

/**
 * Snapshots de las líneas de material del parte.
 *
 * Patrón "isDirty": el bloque de snapshots del material se (re)escribe
 * SOLO al crear la línea o al cambiar `material_id` (mismo criterio que
 * AlbaranLineaMaterialObserver).
 *
 * NO ajusta stock. El parte no consume material; solo cuando se "Genera
 * albarán" desde el parte, el AlbaranLineaMaterialObserver de la línea
 * espejo del albarán hará el ajuste real.
 */
class ParteLineaMaterialObserver
{
    public function saving(ParteLineaMaterial $linea): void
    {
        if (! $linea->isDirty('material_id')) {
            return;
        }

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
}
