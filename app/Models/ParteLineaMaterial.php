<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Línea de material de un parte.
 *
 * Estructura paralela a AlbaranLineaMaterial. Snapshots del material los
 * mantiene ParteLineaMaterialObserver. Al generar albarán desde el parte
 * se clonan estas líneas a `albaran_lineas_material`.
 *
 * @property int $id
 * @property int $parte_id
 * @property int $material_id
 * @property float $cantidad
 * @property string|null $material_descripcion_snapshot
 * @property string|null $material_unidad_medida_snapshot
 * @property string|null $material_numero_pedido_snapshot
 * @property string|null $material_familia_snapshot
 * @property float|null $material_precio_coste_snapshot
 * @property float|null $material_precio_venta_snapshot
 */
class ParteLineaMaterial extends Model
{
    protected $table = 'partes_lineas_material';

    protected $fillable = [
        'parte_id',
        'material_id',
        'cantidad',
        'material_descripcion_snapshot',
        'material_unidad_medida_snapshot',
        'material_numero_pedido_snapshot',
        'material_familia_snapshot',
        'material_precio_coste_snapshot',
        'material_precio_venta_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'material_precio_coste_snapshot' => 'decimal:4',
            'material_precio_venta_snapshot' => 'decimal:4',
        ];
    }

    public function parte(): BelongsTo
    {
        return $this->belongsTo(Parte::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
