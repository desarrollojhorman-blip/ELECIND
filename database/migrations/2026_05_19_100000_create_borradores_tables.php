<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borradores', function (Blueprint $table): void {
            $table->id();

            $table->string('numero_borrador', 60)->unique();

            // Proyecto: FK si existe, texto libre si no
            $table->foreignId('proyecto_id')->nullable()->nullOnDelete()->constrained('proyectos');
            $table->string('proyecto_texto', 255)->nullable();

            // Cliente: FK si existe, texto libre si no
            $table->foreignId('cliente_id')->nullable()->nullOnDelete()->constrained('clientes');
            $table->string('cliente_texto', 255)->nullable();

            // Concepto: FK si existe, texto libre si no
            $table->foreignId('concepto_id')->nullable()->nullOnDelete()->constrained('conceptos');
            $table->string('concepto_texto', 255)->nullable();

            $table->foreignId('responsable_id')->nullable()->nullOnDelete()->constrained('users');

            $table->date('fecha');
            $table->enum('tipo_hora', ['laboral', 'laboral_noche', 'festivo', 'festivo_noche'])->default('laboral');
            $table->enum('estado', ['pendiente', 'convertido'])->default('pendiente');
            $table->text('observaciones')->nullable();

            // Vínculo con el albarán generado (SET NULL si se borra el albarán)
            $table->foreignId('convertido_a_albaran_id')->nullable()->nullOnDelete()->constrained('albaranes');

            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha']);
            $table->index('cliente_id');
            $table->index('proyecto_id');
            $table->index('creado_por');
        });

        Schema::create('borrador_lineas_personal', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('borrador_id')->constrained('borradores')->cascadeOnDelete();

            // Trabajador: FK si existe, texto libre si no
            $table->foreignId('trabajador_id')->nullable()->nullOnDelete()->constrained('users');
            $table->string('trabajador_texto', 255)->nullable();

            $table->decimal('horas', 5, 2)->default(0);
            $table->decimal('horas_extra', 5, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('borrador_lineas_material', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('borrador_id')->constrained('borradores')->cascadeOnDelete();

            // Material: FK si existe, texto libre si no
            $table->foreignId('material_id')->nullable()->nullOnDelete()->constrained('materiales');
            $table->string('material_texto', 255)->nullable();

            $table->decimal('cantidad', 10, 2)->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrador_lineas_material');
        Schema::dropIfExists('borrador_lineas_personal');
        Schema::dropIfExists('borradores');
    }
};
