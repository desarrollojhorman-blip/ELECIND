<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Precios del material:
 *   - precio_coste: lo que cuesta a Elecind comprarlo.
 *   - precio_venta: lo que Elecind factura al cliente.
 *
 * Ambos opcionales (nullable). Dato sensible — visible/editable solo a quien
 * tenga el permiso `materiales.gestionar_precios`.
 *
 * DECIMAL(10,2) → hasta 99.999.999,99 €. Sobra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materiales', function (Blueprint $table): void {
            $table->decimal('precio_coste', 10, 2)->nullable()->after('stock');
            $table->decimal('precio_venta', 10, 2)->nullable()->after('precio_coste');
        });
    }

    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table): void {
            $table->dropColumn(['precio_coste', 'precio_venta']);
        });
    }
};
