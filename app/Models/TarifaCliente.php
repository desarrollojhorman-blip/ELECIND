<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tarifa que se cobra a un cliente por un atributo en un tipo de proyecto.
 *
 * El TarifaClienteObserver registra cada cambio del importe en tarifas_historial
 * con tipo='cliente'.
 */
class TarifaCliente extends Model
{
    protected $table = 'tarifas_cliente';

    protected $fillable = [
        'cliente_id',
        'tipo_proyecto_id',
        'atributo_id',
        'importe',
    ];

    protected $casts = [
        'importe' => 'decimal:4',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipoProyecto(): BelongsTo
    {
        return $this->belongsTo(TiposProyecto::class, 'tipo_proyecto_id');
    }

    public function atributo(): BelongsTo
    {
        return $this->belongsTo(AtributoHora::class, 'atributo_id');
    }
}
