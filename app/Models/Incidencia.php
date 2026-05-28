<?php

namespace App\Models;

use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoIncidencia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int                  $id
 * @property int                  $trabajador_id
 * @property TipoIncidencia       $tipo
 * @property PrioridadIncidencia  $prioridad
 * @property string               $titulo
 * @property string|null          $descripcion
 * @property EstadoIncidencia     $estado
 * @property string|null          $resolucion
 * @property int|null             $resuelto_por
 * @property Carbon|null          $resuelto_at
 * @property-read User            $trabajador
 * @property-read User|null       $resolutor
 */
class Incidencia extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incidencia')
            ->logOnly(['trabajador_id', 'tipo', 'prioridad', 'titulo', 'descripcion', 'estado', 'resolucion', 'resuelto_por'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Incidencia #{$this->id}: {$this->titulo}");
    }

    protected $fillable = [
        'trabajador_id',
        'tipo',
        'prioridad',
        'titulo',
        'descripcion',
        'estado',
        'resolucion',
        'resuelto_por',
        'resuelto_at',
    ];

    protected function casts(): array
    {
        return [
            'tipo'        => TipoIncidencia::class,
            'prioridad'   => PrioridadIncidencia::class,
            'estado'      => EstadoIncidencia::class,
            'resuelto_at' => 'datetime',
        ];
    }

    public function trabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    public function resolutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }
}
