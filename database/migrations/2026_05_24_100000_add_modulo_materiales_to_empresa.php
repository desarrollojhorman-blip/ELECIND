<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo de Materiales activable por instalación.
 *
 * true  → UI completa: pedidos, familias, stock, precios, sección materiales en albarán.
 * false → todo el bloque de Materiales se oculta; los albaranes solo registran horas.
 *
 * Los datos de la tabla `materiales` NO se tocan al flipar el flag — solo cambia la UI.
 * Default true: Elecind (instalación actual) ya usa materiales avanzados y no debe notar nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            $table->boolean('modulo_materiales_avanzado')->default(true)->after('archivo_cantidad_max');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            $table->dropColumn('modulo_materiales_avanzado');
        });
    }
};
