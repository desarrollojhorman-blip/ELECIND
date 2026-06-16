<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla principal de partes de trabajo.
 *
 * Un parte SIEMPRE se crea cuando se captura trabajo en obra (modelo v2:
 * partes/albaranes/borradores como hermanos). Si `es_albaran=true`, además
 * generará/vinculará un albarán facturable (la creación del albarán físico
 * llega en Fase 5; aquí solo registramos el flag).
 *
 * Snapshots: rellenados por ParteObserver al crear/cambiar las FK. Garantizan
 * que cambios futuros en proyectos/clientes/usuarios no muten partes
 * históricos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partes', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 20)->unique();        // PT-2026-0001
            $table->foreignId('user_id')                   // operario captura
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('proyecto_id')
                ->constrained('proyectos')
                ->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->boolean('es_albaran')->default(false);
            $table->foreignId('albaran_id')                // Fase 5
                ->nullable()
                ->constrained('albaranes')
                ->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');

            // Snapshots (rellenados por Observer).
            $table->string('operario_nombre_snapshot')->nullable();
            $table->string('proyecto_nombre_snapshot')->nullable();
            $table->string('proyecto_codigo_snapshot')->nullable();
            $table->unsignedBigInteger('cliente_id_snapshot')->nullable();
            $table->string('cliente_nombre_snapshot')->nullable();
            $table->unsignedBigInteger('tipo_proyecto_id_snapshot')->nullable();
            $table->string('tipo_proyecto_nombre_snapshot')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['fecha', 'user_id']);
            $table->index(['proyecto_id', 'fecha']);
            $table->index(['estado', 'es_albaran']);
            $table->index('cliente_id_snapshot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partes');
    }
};
