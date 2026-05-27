<?php

namespace App\Models;

use App\Services\NumeracionService;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('cliente')
            ->logOnly(['codigo_cliente', 'nombre', 'nombre_comercial', 'cif', 'telefono', 'email', 'activo', 'observaciones'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $_e) => "Cliente: {$this->nombre}");
    }

    protected $table = 'clientes';

    protected $fillable = [
        'codigo_cliente',
        'nombre',
        'nombre_comercial',
        'cif',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'telefono',
        'email',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Al borrar (soft-delete): archivar el código y liberarlo (NULL) para
        // que pueda reutilizarse en otro cliente. No aplica en force delete.
        static::deleted(function (Cliente $cliente): void {
            if ($cliente->isForceDeleting()) {
                return;
            }

            if ($cliente->codigo_cliente !== null) {
                $cliente->codigo_cliente_anterior = $cliente->codigo_cliente;
                $cliente->codigo_cliente = null;
                $cliente->saveQuietly();
            }
        });

        // R2: al restaurar se asigna el SIGUIENTE número libre (el anterior
        // pudo haberse reutilizado). codigo_cliente_anterior se conserva como
        // registro histórico.
        static::restoring(function (Cliente $cliente): void {
            $cliente->codigo_cliente = app(NumeracionService::class)->siguienteNumeroCliente();
        });
    }

    public function proyectos(): HasMany
    {
        return $this->hasMany(Proyecto::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
