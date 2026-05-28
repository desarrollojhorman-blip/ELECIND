<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ausencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('users')->restrictOnDelete();
            $table->string('tipo', 50);          // enum gestionado en PHP
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 50)->default('pendiente');
            $table->text('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ausencias');
    }
};
