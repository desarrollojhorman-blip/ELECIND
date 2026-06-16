<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Líneas de personal de un parte.
 *
 * Modelo LONG (acordado v2): una fila por (trabajador × atributo). Un mismo
 * trabajador puede tener varias líneas en el mismo parte si imputa varios
 * atributos en el día (ej.: 8h Labor + 2h Ex Lab + 1 Plus Retén = 3 filas).
 *
 * Snapshots: el ParteLineaPersonalObserver rellena nombre, atributo, tarifa
 * del cliente y tasa del trabajador al crear o al cambiar las FK. Los partes
 * en estado=cerrado o vinculados a albarán firmado bloquean esta mutación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partes_lineas_personal', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_id')
                ->constrained('partes')
                ->cascadeOnDelete();
            $table->foreignId('user_id')                       // trabajador imputado
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('atributo_id')
                ->constrained('atributos_hora')
                ->restrictOnDelete();
            $table->decimal('cantidad', 6, 2)->default(0);    // horas o unidades de plus
            $table->string('motivo_ajuste')->nullable();      // libre / médico / asuntos…

            // Snapshots del trabajador (al asignar/cambiar).
            $table->string('trabajador_nombre_snapshot')->nullable();
            $table->string('trabajador_apellidos_snapshot')->nullable();
            // Snapshot del atributo (al asignar/cambiar).
            $table->string('atributo_codigo_snapshot', 30)->nullable();
            $table->string('atributo_nombre_snapshot')->nullable();
            // Snapshots económicos (al asignar/cambiar líneas o cabecera).
            $table->decimal('tarifa_snapshot', 8, 4)->default(0);
            $table->decimal('tasa_snapshot', 8, 3)->default(0);
            $table->decimal('facturacion_snapshot', 10, 2)->default(0);
            $table->decimal('coste_snapshot', 10, 2)->default(0);

            $table->timestamps();

            // UNIQUE: un trabajador no debería tener 2 líneas iguales del mismo
            // atributo en el mismo parte (la cantidad va en una sola fila).
            $table->unique(
                ['parte_id', 'user_id', 'atributo_id'],
                'partes_lineas_unique'
            );
            $table->index(['user_id', 'atributo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partes_lineas_personal');
    }
};
