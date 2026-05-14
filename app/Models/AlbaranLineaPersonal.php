<?php

namespace App\Models;

use Database\Factories\AlbaranLineaPersonalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $albaran_id
 * @property int $trabajador_id
 * @property string $horas
 * @property string $horas_extra
 * @property string|null $observaciones
 */
class AlbaranLineaPersonal extends Model
{
    /** @use HasFactory<AlbaranLineaPersonalFactory> */
    use HasFactory;

    protected $table = 'albaran_lineas_personal';

    protected $fillable = [
        'albaran_id',
        'trabajador_id',
        'horas',
        'horas_extra',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'horas' => 'decimal:2',
            'horas_extra' => 'decimal:2',
        ];
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }
}
