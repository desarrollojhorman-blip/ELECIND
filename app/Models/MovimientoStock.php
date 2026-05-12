<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoStock extends Model
{
    use HasFactory;

    protected $table = 'movimientos_stock';

    protected $fillable = [
        'material_lote_id',
        'tipo',
        'cantidad',
        'motivo',
        'usuario_id',
        'albaran_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function materialLote(): BelongsTo
    {
        return $this->belongsTo(MaterialLote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
