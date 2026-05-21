<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot de los precios del material en cada línea de albarán.
 *
 * Se congela junto al resto del snapshot (descripción, unidad, pedido, familia)
 * cuando se crea la línea o cambia el `material_id`. NO se sobreescribe al
 * editar otros campos.
 *
 * En el albarán solo se mostrará `precio_venta`; `precio_coste` queda guardado
 * para informes internos (margen, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->decimal('material_precio_coste_snapshot', 10, 2)->nullable()->after('material_familia_snapshot');
            $table->decimal('material_precio_venta_snapshot', 10, 2)->nullable()->after('material_precio_coste_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->dropColumn([
                'material_precio_coste_snapshot',
                'material_precio_venta_snapshot',
            ]);
        });
    }
};
