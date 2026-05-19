<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorradorLineaPersonal extends Model
{
    protected $table = 'borrador_lineas_personal';

    protected $fillable = [
        'borrador_id',
        'trabajador_id',
        'trabajador_texto',
        'horas',
        'horas_extra',
    ];

    protected $casts = [
        'horas' => 'decimal:2',
        'horas_extra' => 'decimal:2',
    ];

    public function borrador(): BelongsTo
    {
        return $this->belongsTo(Borrador::class);
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    /** Nombre a mostrar (FK o texto libre). */
    public function trabajadorNombre(): string
    {
        if ($this->trabajador !== null) {
            return trim($this->trabajador->nombre.' '.$this->trabajador->apellidos);
        }

        return $this->trabajador_texto ?? '—';
    }
}
