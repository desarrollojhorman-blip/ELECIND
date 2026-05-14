<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Refactorizar albaran_lineas_material: lote_id → material_id ──
        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->foreignId('material_id')
                ->after('albaran_id')
                ->constrained('materiales')
                ->restrictOnDelete();
        });

        // Copiar referencia si hubiera datos (entorno dev: no hay, pero es correcto)
        // DB::statement('UPDATE albaran_lineas_material SET material_id = (SELECT material_id FROM material_lotes WHERE id = albaran_lineas_material.material_lote_id)');

        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->dropForeign(['material_lote_id']);
            $table->dropIndex(['material_lote_id']);
            $table->dropColumn('material_lote_id');
        });

        // ── 2. Eliminar movimientos_stock y material_lotes ───────────────
        Schema::dropIfExists('movimientos_stock');
        Schema::dropIfExists('material_lotes');
    }

    public function down(): void
    {
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
        });

        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->foreignId('material_lote_id')
                ->nullable()
                ->after('albaran_id')
                ->constrained('material_lotes')
                ->nullOnDelete();
            $table->dropForeign(['material_id']);
            $table->dropColumn('material_id');
        });
    }
};
