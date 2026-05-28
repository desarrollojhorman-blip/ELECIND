<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo', 50);
            $table->string('prioridad', 20)->default('media');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->string('estado', 50)->default('pendiente');
            $table->text('resolucion')->nullable();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resuelto_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
