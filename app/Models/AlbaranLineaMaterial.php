<?php

namespace App\Models;

use Database\Factories\AlbaranLineaMaterialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $albaran_id
 * @property int $material_lote_id
 * @property string $cantidad
 * @property string|null $observaciones
 *
 * Nota: el descuento de stock en el lote se gestiona automáticamente vía
 * AlbaranLineaMaterialObserver (creado/actualizado/eliminado).
 */
class AlbaranLineaMaterial extends Model
{
    /** @use HasFactory<AlbaranLineaMaterialFactory> */
    use HasFactory;

    protected $table = 'albaran_lineas_material';

    protected $fillable = [
        'albaran_id',
        'material_lote_id',
        'cantidad',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
        ];
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(MaterialLote::class, 'material_lote_id');
    }
}
