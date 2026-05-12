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
            $table->string('nombre_comercial')->nullable();
            $table->string('cif')->nullable()->unique();
            $table->string('direccion')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion')->nullable();
            $table->string('provincia')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('correo_notificaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['nombre', 'activo']);
        });

        Schema::create('tipos_proyectos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('color', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique('nombre');
        });

        Schema::create('proyectos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_cliente_id')->constrained('empresas_clientes')->cascadeOnDelete();
            $table->foreignId('tipo_proyecto_id')->nullable()->constrained('tipos_proyectos')->nullOnDelete();
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->enum('estado', ['borrador', 'activo', 'cerrado', 'archivado'])->default('borrador');
            $table->foreignId('responsable_principal_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['empresa_cliente_id', 'codigo']);
            $table->index(['nombre', 'estado']);
        });

        Schema::create('conceptos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('proyecto_usuario', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rol_en_proyecto')->nullable();
            $table->timestamps();

            $table->unique(['proyecto_id', 'user_id']);
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
            $table->string('codigo')->nullable()->unique();
            $table->string('grupo')->nullable();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida')->default('ud');
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->boolean('notificar_stock_bajo')->default(true);
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['grupo', 'nombre']);
        });

        Schema::create('material_proyecto', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained('materiales')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->decimal('cantidad_prevista', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['material_id', 'proyecto_id']);
        });

        Schema::create('material_lotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_id')->constrained('materiales')->cascadeOnDelete();
            $table->string('codigo_lote')->nullable()->unique();
            $table->string('proveedor')->nullable();
            $table->string('n_pedido')->nullable();
            $table->decimal('stock_disponible', 10, 2)->default(0);
            $table->decimal('stock_inicial', 10, 2)->default(0);
            $table->date('fecha_entrada')->nullable();
            $table->date('fecha_caducidad')->nullable();
            $table->decimal('stock_minimo_lote', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['material_id', 'fecha_entrada']);
        });

        Schema::create('movimientos_stock', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('material_lote_id')->constrained('material_lotes')->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 2);
            $table->string('motivo')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('albaran_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_stock');
        Schema::dropIfExists('material_lotes');
        Schema::dropIfExists('material_proyecto');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('proyecto_concepto');
        Schema::dropIfExists('proyecto_usuario');
        Schema::dropIfExists('conceptos');
        Schema::dropIfExists('proyectos');
        Schema::dropIfExists('tipos_proyectos');
        Schema::dropIfExists('empresas_clientes');
    }
};
