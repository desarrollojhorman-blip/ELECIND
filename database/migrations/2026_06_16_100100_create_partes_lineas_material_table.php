<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Líneas de material de un parte.
 *
 * Estructura paralela a `albaran_lineas_material`: una fila por (parte ×
 * material) con cantidad y snapshots del material (descripción, unidad,
 * familia, nº pedido, precios coste/venta).
 *
 * Al "Generar albarán" desde el parte se copian estos snapshots a la tabla
 * `albaran_lineas_material` (el parte conserva los suyos).
 *
 * Decisión: no se ajusta stock desde el parte. El ajuste solo ocurre cuando
 * se crea el albarán (Fase: generarAlbaran). Eso evita que un parte abierto
 * que después se descarta haya consumido stock fantasma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partes_lineas_material', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_id')
                ->constrained('partes')
                ->cascadeOnDelete();
            $table->foreignId('material_id')
                ->constrained('materiales')
                ->restrictOnDelete();
            $table->decimal('cantidad', 10, 2)->default(0);

            // Snapshots del material (al asignar/cambiar).
            $table->string('material_descripcion_snapshot')->nullable();
            $table->string('material_unidad_medida_snapshot', 30)->nullable();
            $table->string('material_numero_pedido_snapshot')->nullable();
            $table->string('material_familia_snapshot')->nullable();
            $table->decimal('material_precio_coste_snapshot', 10, 4)->nullable();
            $table->decimal('material_precio_venta_snapshot', 10, 4)->nullable();

            $table->timestamps();

            $table->unique(['parte_id', 'material_id'], 'partes_lineas_material_unique');
            $table->index('material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partes_lineas_material');
    }
};
