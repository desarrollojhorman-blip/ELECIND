<?php

namespace App\Models;

use App\Services\NumeracionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proyecto extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('proyecto')
            ->logOnly(['nombre', 'codigo', 'estado', 'cliente_id', 'tipo_proyecto_id', 'descripcion', 'fecha_inicio', 'fecha_fin', 'responsable_principal_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Proyecto: {$this->nombre}");
    }

    protected $fillable = [
        'cliente_id',
        'tipo_proyecto_id',
        'nombre',
        'codigo',
        'codigo_secuencial',
        'codigo_borrador',
        'numero_borrador',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'responsable_principal_id',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $proyecto): void {
            if ($proyecto->isForceDeleting()) {
                return;
            }
            $proyecto->codigo_borrador = $proyecto->codigo;
            $proyecto->numero_borrador = $proyecto->codigo_secuencial;
            $proyecto->codigo = null;
            $proyecto->codigo_secuencial = null;
            $proyecto->saveQuietly();
        });

        static::restoring(function (self $proyecto): void {
            $codigo = null;
            $secuencial = null;

            if ($proyecto->codigo_borrador !== null) {
                $ocupado = self::query()
                    ->where('codigo', $proyecto->codigo_borrador)
                    ->exists();

                if (! $ocupado) {
                    $codigo = $proyecto->codigo_borrador;
                    $secuencial = $proyecto->numero_borrador;
                }
            }

            if ($codigo === null) {
                $resultado = app(NumeracionService::class)->siguienteProyecto();
                $codigo = $resultado['codigo'];
                $secuencial = $resultado['secuencial'];
            }

            $proyecto->codigo = $codigo;
            $proyecto->codigo_secuencial = $secuencial;
            $proyecto->codigo_borrador = null;
            $proyecto->numero_borrador = null;
        });
    }

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function albaranes(): HasMany
    {
        return $this->hasMany(Albaran::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipoProyecto(): BelongsTo
    {
        return $this->belongsTo(TiposProyecto::class, 'tipo_proyecto_id');
    }

    public function responsablePrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_principal_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'proyecto_usuario')
            ->withPivot('rol_en_proyecto')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Concepto, $this>
     */
    public function conceptos(): BelongsToMany
    {
        return $this->belongsToMany(Concepto::class, 'proyecto_concepto')->withTimestamps();
    }

    /**
     * @return BelongsToMany<Material, $this>
     */
    public function materiales(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'material_proyecto')->withTimestamps();
    }
}
