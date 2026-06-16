<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial UNIFICADO de cambios en tarifas (cliente + trabajador).
 *
 * Una sola tabla para auditar ambos lados:
 *   - tipo = 'cliente'     → referencia_id apunta a tarifas_cliente.id
 *   - tipo = 'trabajador'  → referencia_id apunta a users.id
 *
 * Lo escriben automáticamente los Observers cuando detectan cambios en
 * el importe (tarifa_cliente) o en cualquier columna tasa_* (users).
 *
 * La vista "Tarifas → Historial" lee de aquí con filtros opcionales por tipo.
 *
 * NO usamos FKs físicas en referencia_id porque referencia dos tablas
 * distintas según `tipo`. La aplicación garantiza la integridad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas_historial', function (Blueprint $table): void {
            $table->id();
            $table->enum('tipo', ['cliente', 'trabajador']);
            $table->unsignedBigInteger('referencia_id');
            $table->foreignId('atributo_id')->constrained('atributos_hora')->cascadeOnDelete();
            $table->decimal('importe_anterior', 8, 4)->default(0);
            $table->decimal('importe_nuevo', 8, 4)->default(0);
            $table->foreignId('cambiado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motivo')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tipo', 'referencia_id']);
            $table->index('atributo_id');
            $table->index('cambiado_por');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas_historial');
    }
};
