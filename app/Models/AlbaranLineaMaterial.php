<?php

namespace App\Models;

use Database\Factories\AlbaranLineaMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $albaran_id
 * @property int $material_id
 * @property string $cantidad
 * @property string|null $material_descripcion_snapshot
 * @property string|null $material_unidad_medida_snapshot
 * @property string|null $material_numero_pedido_snapshot
 * @property string|null $material_familia_snapshot
 * @property string|null $material_precio_coste_snapshot
 * @property string|null $material_precio_venta_snapshot
 *
 * Nota: el descuento de stock en el material se gestiona automáticamente vía
 * AlbaranLineaMaterialObserver (creado/actualizado/eliminado).
 */
class AlbaranLineaMaterial extends Model
{
    /** @use HasFactory<AlbaranLineaMaterialFactory> */
    use HasFactory;

    protected $table = 'albaran_lineas_material';

    protected $fillable = [
        'albaran_id',
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
            'material_precio_coste_snapshot' => 'decimal:2',
            'material_precio_venta_snapshot' => 'decimal:2',
        ];
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
