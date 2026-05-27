<?php

namespace App\Models;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoHora;
use Database\Factories\AlbaranFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $numero
 * @property Carbon $fecha
 * @property int $cliente_id
 * @property int|null $proyecto_id
 * @property int|null $concepto_id
 * @property int $creado_por
 * @property int|null $responsable_id
 * @property EstadoAlbaran $estado
 * @property TipoHora $tipo_hora
 * @property string|null $observaciones
 * @property array<string, mixed>|null $snapshot_data
 * @property-read EloquentCollection<int, AlbaranLineaPersonal> $lineasPersonal
 * @property-read EloquentCollection<int, AlbaranLineaMaterial> $lineasMaterial
 * @property-read EloquentCollection<int, Firma> $firmas
 * @property-read EloquentCollection<int, TokenFirma> $tokensFirma
 * @property-read EloquentCollection<int, AlbaranArchivo> $archivos
 */
class Albaran extends Model
{
    /** @use HasFactory<AlbaranFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('albaran')
            ->logOnly(['numero', 'fecha', 'cliente_id', 'proyecto_id', 'concepto_id', 'creado_por', 'responsable_id', 'estado', 'tipo_hora', 'observaciones'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Albarán #{$this->numero}");
    }

    protected $table = 'albaranes';

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
        'snapshot_data',
        'firma_trabajador_user_id',
        'firma_trabajador_otro_nombre',
        'firma_trabajador_otro_correo',
        'firma_responsable_otro_nombre',
        'firma_responsable_otro_correo',
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
            'estado' => EstadoAlbaran::class,
            'tipo_hora' => TipoHora::class,
            'snapshot_data' => 'array',
            'es_personalizado' => 'boolean',
        ];
    }

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

    public function firmaTrabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'firma_trabajador_user_id');
    }

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(AlbaranLineaPersonal::class);
    }

    public function lineasMaterial(): HasMany
    {
        return $this->hasMany(AlbaranLineaMaterial::class);
    }

    public function firmas(): MorphMany
    {
        return $this->morphMany(Firma::class, 'firmable');
    }

    public function tokensFirma(): MorphMany
    {
        return $this->morphMany(TokenFirma::class, 'firmable');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(AlbaranArchivo::class);
    }

    /**
     * ¿El albarán tiene firma del tipo indicado?
     */
    public function tieneFirma(string $tipo): bool
    {
        return $this->firmas()->where('tipo', $tipo)->exists();
    }
}
