<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Parte de trabajo.
 *
 * Entidad central de v2: TODA captura de trabajo crea un parte. Si
 * `es_albaran=true` además se generará/vinculará un albarán facturable
 * (la vinculación llega en Fase 5).
 *
 * Los snapshots los mantiene ParteObserver al cambiar las FK
 * correspondientes — mismo patrón que Albaran.
 *
 * @property int $id
 * @property string $codigo
 * @property int $user_id
 * @property int $proyecto_id
 * @property Carbon $fecha
 * @property string|null $hora_inicio
 * @property string|null $hora_fin
 * @property bool $es_albaran
 * @property int|null $albaran_id
 * @property string|null $observaciones
 * @property string $estado
 * @property string|null $operario_nombre_snapshot
 * @property string|null $proyecto_nombre_snapshot
 * @property string|null $proyecto_codigo_snapshot
 * @property int|null $cliente_id_snapshot
 * @property string|null $cliente_nombre_snapshot
 * @property int|null $tipo_proyecto_id_snapshot
 * @property string|null $tipo_proyecto_nombre_snapshot
 * @property-read EloquentCollection<int, ParteLineaPersonal> $lineasPersonal
 */
class Parte extends Model
{
    use LogsActivity, SoftDeletes;

    public const ESTADO_ABIERTO = 'abierto';

    public const ESTADO_CERRADO = 'cerrado';

    protected $table = 'partes';

    protected $fillable = [
        'codigo',
        'user_id',
        'proyecto_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'es_albaran',
        'albaran_id',
        'observaciones',
        'estado',
        // Snapshots
        'operario_nombre_snapshot',
        'proyecto_nombre_snapshot',
        'proyecto_codigo_snapshot',
        'cliente_id_snapshot',
        'cliente_nombre_snapshot',
        'tipo_proyecto_id_snapshot',
        'tipo_proyecto_nombre_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'es_albaran' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('parte')
            ->logOnly([
                'codigo', 'user_id', 'proyecto_id', 'fecha',
                'es_albaran', 'albaran_id', 'estado', 'observaciones',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Parte #{$this->codigo}");
    }

    /* ── Relaciones ─────────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(ParteLineaPersonal::class);
    }

    /* ── Scopes ─────────────────────────────────────────────── */

    public function scopeAbiertos(Builder $q): Builder
    {
        return $q->where('estado', self::ESTADO_ABIERTO);
    }

    public function scopeCerrados(Builder $q): Builder
    {
        return $q->where('estado', self::ESTADO_CERRADO);
    }

    public function scopeDeOperario(Builder $q, int $userId): Builder
    {
        return $q->where('user_id', $userId);
    }

    /* ── Accesors ──────────────────────────────────────────── */

    /** Horas totales imputadas (suma de líneas de tipos de hora — excluye pluses). */
    public function horasTotales(): float
    {
        return (float) $this->lineasPersonal
            ->filter(fn (ParteLineaPersonal $l) => $l->atributo?->esHora() ?? false)
            ->sum('cantidad');
    }

    public function facturacionTotal(): float
    {
        return (float) $this->lineasPersonal->sum('facturacion_snapshot');
    }

    public function costeTotal(): float
    {
        return (float) $this->lineasPersonal->sum('coste_snapshot');
    }

    public function margenTotal(): float
    {
        return $this->facturacionTotal() - $this->costeTotal();
    }

    public function esEditable(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO;
    }
}
