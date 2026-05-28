<?php

namespace App\Models;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int             $id
 * @property int             $trabajador_id
 * @property TipoAusencia    $tipo
 * @property Carbon          $fecha_inicio
 * @property Carbon          $fecha_fin
 * @property EstadoAusencia  $estado
 * @property string|null     $motivo
 * @property string|null     $observaciones
 * @property int|null        $aprobado_por
 * @property Carbon|null     $aprobado_at
 * @property-read User       $trabajador
 * @property-read User|null  $aprobador
 */
class Ausencia extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ausencia')
            ->logOnly(['trabajador_id', 'tipo', 'fecha_inicio', 'fecha_fin', 'estado', 'motivo', 'observaciones', 'aprobado_por'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Ausencia #{$this->id}");
    }

    protected $fillable = [
        'trabajador_id',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo',
        'observaciones',
        'aprobado_por',
        'aprobado_at',
    ];

    protected function casts(): array
    {
        return [
            'tipo'        => TipoAusencia::class,
            'estado'      => EstadoAusencia::class,
            'fecha_inicio' => 'date',
            'fecha_fin'   => 'date',
            'aprobado_at' => 'datetime',
        ];
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /** Días naturales que cubre la ausencia (inclusive en ambos extremos). */
    public function diasNaturales(): int
    {
        return (int) $this->fecha_inicio->diffInDays($this->fecha_fin) + 1;
    }
}
