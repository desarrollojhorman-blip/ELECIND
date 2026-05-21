<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshots del material en cada línea de material del albarán.
 *
 * Congela descripción, unidad de medida, número de pedido al que pertenecía
 * y la familia. FK `material_id` se mantiene; estos campos son ADICIONALES.
 * `numero_pedido` y `familia` son textos cogidos del pedido/familia del
 * material en el momento (NO son FKs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->text('material_descripcion_snapshot')->nullable()->after('cantidad');
            $table->string('material_unidad_medida_snapshot', 30)->nullable()->after('material_descripcion_snapshot');
            $table->string('material_numero_pedido_snapshot', 50)->nullable()->after('material_unidad_medida_snapshot');
            $table->string('material_familia_snapshot', 150)->nullable()->after('material_numero_pedido_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('albaran_lineas_material', function (Blueprint $table): void {
            $table->dropColumn([
                'material_descripcion_snapshot',
                'material_unidad_medida_snapshot',
                'material_numero_pedido_snapshot',
                'material_familia_snapshot',
            ]);
        });
    }
};
