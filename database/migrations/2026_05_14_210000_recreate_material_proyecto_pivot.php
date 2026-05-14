<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recrea el pivot material_proyecto que la migración 200000 había eliminado
 * por error junto con el resto del refactor de materiales.
 *
 * En el modelo de negocio, los materiales se asignan a proyectos (qué materiales
 * están "disponibles" para usar en albaranes de un proyecto concreto). El pivot
 * actual es simple: solo material_id + proyecto_id, sin cantidad_prevista
 * (esa columna no aportaba valor en uso real, se quita).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_proyecto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained('materiales')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['material_id', 'proyecto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_proyecto');
    }
};
