<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Familias de material: agrupador opcional de materiales del catálogo.
 *
 * Caso de uso: el mismo "cable H07V-K rojo" entra en distintos pedidos con
 * descripciones ligeramente diferentes. La familia permite agruparlos para
 * filtros y reportes.
 *
 * El campo familia_id en materiales es NULLABLE (no obligatorio) y al borrar
 * una familia los materiales asociados quedan sin familia (nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('familias_material', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('materiales', function (Blueprint $table): void {
            $table->foreignId('familia_id')
                ->nullable()
                ->after('numero_pedido_id')
                ->constrained('familias_material')
                ->nullOnDelete();

            $table->index('familia_id');
        });
    }

    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table): void {
            $table->dropForeign(['familia_id']);
            $table->dropIndex(['familia_id']);
            $table->dropColumn('familia_id');
        });

        Schema::dropIfExists('familias_material');
    }
};
