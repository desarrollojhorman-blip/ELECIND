<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tabla numero_pedidos ─────────────────────────────────────
        Schema::create('numero_pedidos', function (Blueprint $table): void {
            $table->id();
            $table->string('numero')->unique();
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->string('proveedor')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['numero', 'fecha']);
        });

        // ── 2. Refactorizar tabla materiales ───────────────────────────
        // Nota: hay que dropear explícitamente el índice unique de `codigo`
        // antes del dropColumn para que SQLite (entorno de tests) no se queje.
        Schema::table('materiales', function (Blueprint $table): void {
            $table->dropUnique('materiales_codigo_unique');
            $table->dropIndex(['grupo', 'nombre']);
        });

        Schema::table('materiales', function (Blueprint $table): void {
            // Quitar columnas antiguas
            $table->dropColumn(['codigo', 'grupo', 'nombre', 'stock_minimo', 'notificar_stock_bajo', 'activo']);

            // Añadir nuevas columnas
            $table->foreignId('numero_pedido_id')
                ->after('id')
                ->constrained('numero_pedidos')
                ->restrictOnDelete();

            $table->decimal('stock', 10, 2)->default(0)->after('unidad_medida');

            $table->index('numero_pedido_id');
        });

        // ── 3. Eliminar tabla material_proyecto ─────────────────────────
        Schema::dropIfExists('material_proyecto');
    }

    public function down(): void
    {
        Schema::dropIfExists('material_proyecto');

        Schema::table('materiales', function (Blueprint $table): void {
            $table->dropForeign(['numero_pedido_id']);
            $table->dropIndex(['numero_pedido_id']);
            $table->dropColumn(['numero_pedido_id', 'stock']);

            $table->string('codigo')->nullable()->unique();
            $table->string('grupo')->nullable()->after('id');
            $table->string('nombre')->after('grupo');
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->boolean('notificar_stock_bajo')->default(true);
            $table->boolean('activo')->default(true);
        });

        Schema::dropIfExists('numero_pedidos');
    }
};
