<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas_clientes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('cif')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('correo_notificaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('nombre');
        });

        Schema::create('tipos_proyecto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_cliente_id')->constrained('empresas_clientes')->cascadeOnDelete();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('proyectos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tipo_proyecto_id')->constrained('tipos_proyecto')->cascadeOnDelete();
            $table->string('nombre');
            $table->foreignId('responsable_principal_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('nombre');
        });

        Schema::create('conceptos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('proyecto_usuario', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['proyecto_id', 'usuario_id']);
        });

        Schema::create('proyecto_concepto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('concepto_id')->constrained('conceptos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['proyecto_id', 'concepto_id']);
        });

        Schema::create('materiales', function (Blueprint $table): void {
            $table->id();
            $table->string('grupo')->nullable();
            $table->string('nombre');
            $table->string('unidad_medida')->default('ud');
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->boolean('notificar_stock_bajo')->default(true);
            $table->timestamps();

            $table->index(['grupo', 'nombre']);
        });

        Schema::create('material_lotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained('materiales')->cascadeOnDelete();
            $table->string('proveedor')->nullable();
            $table->string('n_pedido')->nullable();
            $table->decimal('stock_disponible', 10, 2)->default(0);
            $table->decimal('stock_inicial', 10, 2)->default(0);
            $table->date('fecha_entrada')->nullable();
            $table->decimal('stock_minimo_lote', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('movimientos_stock', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_lote_id')->constrained('material_lotes')->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('albaran_id')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
        Schema::dropIfExists('material_lotes');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('proyecto_concepto');
        Schema::dropIfExists('proyecto_usuario');
        Schema::dropIfExists('conceptos');
        Schema::dropIfExists('proyectos');
        Schema::dropIfExists('tipos_proyecto');
        Schema::dropIfExists('empresas_clientes');
    }
};
