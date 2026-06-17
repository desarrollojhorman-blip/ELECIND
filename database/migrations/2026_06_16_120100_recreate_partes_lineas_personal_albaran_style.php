<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rehace `partes_lineas_personal` con el mismo esquema que
 * `albaran_lineas_personal`.
 *
 * Pasa del modelo "atributo fino + cantidad" al modelo "trabajador +
 * horas + horas_extra" para que el flujo "generar albarán" sea una
 * copia 1:1 de cabecera + líneas.
 *
 * El cálculo de tarifa/coste se hace en runtime contra el catálogo v2
 * (tarifas_cliente) cuando se requiera reporte; las líneas ya no
 * guardan snapshots económicos pero sí las tasas vigentes del
 * trabajador al momento (mismo patrón que albaran_lineas_personal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('partes_lineas_personal');

        Schema::create('partes_lineas_personal', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_id')
                ->constrained('partes')
                ->cascadeOnDelete();
            $table->foreignId('trabajador_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->decimal('horas', 5, 2)->default(0);
            $table->decimal('horas_extra', 5, 2)->default(0);

            // Snapshots del trabajador (al asignar/cambiar).
            $table->string('trabajador_nombre_snapshot')->nullable();
            $table->string('trabajador_apellidos_snapshot')->nullable();
            $table->string('trabajador_numero_empleado_snapshot')->nullable();
            $table->decimal('trabajador_tasa_hora_snapshot', 8, 3)->nullable();
            $table->decimal('trabajador_tasa_extra_snapshot', 8, 3)->nullable();
            $table->decimal('trabajador_tasa_festivo_snapshot', 8, 3)->nullable();

            $table->timestamps();

            $table->index(['parte_id', 'trabajador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partes_lineas_personal');

        // Restaurar el esquema previo (modelo v2 con atributo).
        Schema::create('partes_lineas_personal', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_id')->constrained('partes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('atributo_id')->constrained('atributos_hora')->restrictOnDelete();
            $table->decimal('cantidad', 6, 2)->default(0);
            $table->string('motivo_ajuste')->nullable();
            $table->string('trabajador_nombre_snapshot')->nullable();
            $table->string('trabajador_apellidos_snapshot')->nullable();
            $table->string('atributo_codigo_snapshot', 30)->nullable();
            $table->string('atributo_nombre_snapshot')->nullable();
            $table->decimal('tarifa_snapshot', 8, 4)->default(0);
            $table->decimal('tasa_snapshot', 8, 3)->default(0);
            $table->decimal('facturacion_snapshot', 10, 2)->default(0);
            $table->decimal('coste_snapshot', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['parte_id', 'user_id', 'atributo_id'], 'partes_lineas_unique');
        });
    }
};
