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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
 * @property-read EloquentCollection<int, AlbaranFirma> $firmas
 * @property-read EloquentCollection<int, AlbaranTokenFirma> $tokensFirma
 */
class Albaran extends Model
{
    /** @use HasFactory<AlbaranFactory> */
    use HasFactory, SoftDeletes;

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
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'estado' => EstadoAlbaran::class,
            'tipo_hora' => TipoHora::class,
            'snapshot_data' => 'array',
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

    public function lineasPersonal(): HasMany
    {
        return $this->hasMany(AlbaranLineaPersonal::class);
    }

    public function lineasMaterial(): HasMany
    {
        return $this->hasMany(AlbaranLineaMaterial::class);
    }

    public function firmas(): HasMany
    {
        return $this->hasMany(AlbaranFirma::class);
    }

    public function tokensFirma(): HasMany
    {
        return $this->hasMany(AlbaranTokenFirma::class);
    }

    /**
     * ¿El albarán tiene firma del tipo indicado?
     */
    public function tieneFirma(string $tipo): bool
    {
        return $this->firmas()->where('tipo', $tipo)->exists();
    }
}
