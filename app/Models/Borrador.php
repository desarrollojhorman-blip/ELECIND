<?php

namespace App\Models;

use App\Enums\TipoHora;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property-read EloquentCollection<int, Firma> $firmas
 * @property-read EloquentCollection<int, TokenFirma> $tokensFirma
 */
class Borrador extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'borradores';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('borrador')
            ->logOnly(['numero_borrador', 'proyecto_id', 'cliente_id', 'concepto_id', 'responsable_id', 'fecha', 'tipo_hora', 'estado', 'observaciones', 'convertido_a_albaran_id', 'creado_por'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Borrador #{$this->numero_borrador}");
    }

    protected $fillable = [
        'numero_borrador',
        'proyecto_id',
        'proyecto_texto',
        'cliente_id',
        'cliente_texto',
        'concepto_id',
        'concepto_texto',
        'responsable_id',
        'responsable_texto',
        'fecha',
        'tipo_hora',
        'estado',
        'observaciones',
        'convertido_a_albaran_id',
        'convertido_a_parte_id',
        'creado_por',
        'firma_trabajador_user_id',
        'firma_trabajador_otro_nombre',
        'firma_trabajador_otro_correo',
        'firma_responsable_otro_nombre',
        'firma_responsable_otro_correo',
        'tiene_plus_retencion',
        'crear_albaran',
    ];

    protected $casts = [
        'fecha'               => 'date',
        'tipo_hora'           => TipoHora::class,
        'tiene_plus_retencion' => 'boolean',
        'crear_albaran'       => 'boolean',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function concepto(): BelongsTo
    {
        return $this->belongsTo(Concepto::class);
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function firmaTrabajador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'firma_trabajador_user_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function albaranConvertido(): BelongsTo
    {
        return $this->belongsTo(Albaran::class, 'convertido_a_albaran_id');
    }

    public function parteConvertido(): BelongsTo
    {
        return $this->belongsTo(Parte::class, 'convertido_a_parte_id');
    }

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(BorradorLineaPersonal::class);
    }

    public function lineasMaterial(): HasMany
    {
        return $this->hasMany(BorradorLineaMaterial::class);
    }

    public function firmas(): MorphMany
    {
        return $this->morphMany(Firma::class, 'firmable');
    }

    public function tokensFirma(): MorphMany
    {
        return $this->morphMany(TokenFirma::class, 'firmable');
    }

    public function estaConvertido(): bool
    {
        return $this->estado === 'convertido';
    }

    public function tieneFirma(string $tipo): bool
    {
        return $this->firmas()->where('tipo', $tipo)->exists();
    }

    /** Número a mostrar (alias de numero_borrador). */
    public function getNumeroAttribute(): string
    {
        return $this->numero_borrador;
    }

    /** Nombre a mostrar del proyecto (FK o texto libre). */
    public function proyectoNombre(): string
    {
        return $this->proyecto?->nombre ?? $this->proyecto_texto ?? '—';
    }

    /** Nombre a mostrar del cliente (FK o texto libre). */
    public function clienteNombre(): string
    {
        return $this->cliente?->nombre ?? $this->cliente_texto ?? '—';
    }

    /** Nombre a mostrar del concepto (FK o texto libre). */
    public function conceptoNombre(): string
    {
        return $this->concepto?->nombre ?? $this->concepto_texto ?? '—';
    }
}
