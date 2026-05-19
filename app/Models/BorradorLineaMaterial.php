<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorradorLineaMaterial extends Model
{
    protected $table = 'borrador_lineas_material';

    protected $fillable = [
        'borrador_id',
        'material_id',
        'material_texto',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function borrador(): BelongsTo
    {
        return $this->belongsTo(Borrador::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /** Nombre a mostrar (FK o texto libre). */
    public function materialNombre(): string
    {
        return $this->material?->descripcion ?? $this->material_texto ?? '—';
    }
}
