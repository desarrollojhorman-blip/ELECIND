<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tarifas que se COBRAN al cliente.
 *
 * Una fila por (cliente, tipo_proyecto, atributo) con su importe.
 * - Para tipos de hora: importe = €/h.
 * - Para pluses: importe = flat por unidad de plus.
 *
 * UNIQUE compuesto evita duplicar tarifas para la misma combinación.
 *
 * La presencia/ausencia de filas decide qué atributos puede facturarse a un
 * cliente: si VESTAS no tiene fila para PLUS_RETEN, no se le puede imputar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_cliente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('tipo_proyecto_id')->constrained('tipos_proyectos')->cascadeOnDelete();
            $table->foreignId('atributo_id')->constrained('atributos_hora')->cascadeOnDelete();
            $table->decimal('importe', 8, 4)->default(0);
            $table->timestamps();

            $table->unique(
                ['cliente_id', 'tipo_proyecto_id', 'atributo_id'],
                'tarifas_cliente_unique'
            );
            $table->index(['cliente_id', 'tipo_proyecto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_cliente');
    }
};
