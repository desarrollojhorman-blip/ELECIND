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
 * Mismo esquema que Albaran (incluida la cabecera con cliente/proyecto/
 * concepto/responsable/tipo_hora/observaciones, los snapshots y el modo
 * "parte personalizado" con textos libres). Diferencias:
 *
 *  - No tiene firmas ni archivos.
 *  - Estado: 'abierto' | 'cerrado' (un parte se "cierra" al generar albarán).
 *  - `albaran_id` enlaza al albarán generado (Fase 5: vinculación bidireccional).
 *
 * @property int $id
 * @property string $numero
 * @property Carbon $fecha
 * @property int|null $cliente_id
 * @property int|null $proyecto_id
 * @property int|null $concepto_id
 * @property int|null $creado_por
 * @property int|null $responsable_id
 * @property string $estado
 * @property string $tipo_hora
 * @property string|null $observaciones
 * @property int|null $albaran_id
 * @property bool $es_personalizado
 * @property array<string, mixed>|null $snapshot_data
 * @property-read EloquentCollection<int, ParteLineaPersonal> $lineasPersonal
 * @property-read EloquentCollection<int, ParteLineaMaterial> $lineasMaterial
 */
class Parte extends Model
{
    use LogsActivity, SoftDeletes;

    public const ESTADO_ABIERTO = 'abierto';

    public const ESTADO_CERRADO = 'cerrado';

    protected $table = 'partes';

    protected $fillable = [
        'numero',
        'fecha',
        'cliente_id',
        'proyecto_id',
        'concepto_id',
        'creado_por',
        'responsable_id',
        'estado',
        'tipo_hora',
        'observaciones',
        'albaran_id',
        'snapshot_data',
        // Snapshots cabecera
        'cliente_codigo_snapshot',
        'cliente_nombre_snapshot',
        'cliente_cif_snapshot',
        'proyecto_codigo_snapshot',
        'proyecto_nombre_snapshot',
        'concepto_nombre_snapshot',
        'creador_username_snapshot',
        'creador_nombre_snapshot',
        'creador_apellidos_snapshot',
        'creador_numero_empleado_snapshot',
        'responsable_username_snapshot',
        'responsable_nombre_snapshot',
        'responsable_apellidos_snapshot',
        'responsable_numero_empleado_snapshot',
        // Parte personalizado
        'es_personalizado',
        'cliente_texto',
        'proyecto_texto',
        'concepto_texto',
        'responsable_texto',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'snapshot_data' => 'array',
            'es_personalizado' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('parte')
            ->logOnly(['numero', 'fecha', 'cliente_id', 'proyecto_id', 'concepto_id', 'creado_por', 'responsable_id', 'estado', 'tipo_hora', 'observaciones', 'albaran_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Parte #{$this->numero}");
    }

    /* ── Relaciones ─────────────────────────────────────────── */

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Concepto::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function albaran(): BelongsTo
    {
        return $this->belongsTo(Albaran::class);
    }

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(ParteLineaPersonal::class);
    }

    public function lineasMaterial(): HasMany
    {
        return $this->hasMany(ParteLineaMaterial::class);
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

    public function scopeConAlbaran(Builder $q): Builder
    {
        return $q->whereNotNull('albaran_id');
    }

    public function scopeSinAlbaran(Builder $q): Builder
    {
        return $q->whereNull('albaran_id');
    }

    /* ── Helpers ───────────────────────────────────────────── */

    public function esEditable(): bool
    {
        return $this->estado === self::ESTADO_ABIERTO && ! $this->tieneAlbaran();
    }

    public function tieneAlbaran(): bool
    {
        return $this->albaran_id !== null;
    }

    public function horasTotales(): float
    {
        return (float) $this->lineasPersonal->sum(fn (ParteLineaPersonal $l) => (float) $l->horas + (float) $l->horas_extra);
    }
}
