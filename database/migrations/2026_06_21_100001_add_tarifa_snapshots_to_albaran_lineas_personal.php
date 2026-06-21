<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade columnas de snapshot económico a albaran_lineas_personal.
 *
 * Mismas cuatro columnas que en partes_lineas_personal:
 *   tarifa_hora_snapshot, tarifa_extra_snapshot, facturacion_snapshot, coste_snapshot.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->decimal('tarifa_hora_snapshot', 8, 4)->nullable()->after('trabajador_tasa_festivo_snapshot');
            $table->decimal('tarifa_extra_snapshot', 8, 4)->nullable()->after('tarifa_hora_snapshot');
            $table->decimal('facturacion_snapshot', 10, 2)->nullable()->after('tarifa_extra_snapshot');
            $table->decimal('coste_snapshot', 10, 2)->nullable()->after('facturacion_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->dropColumn(['tarifa_hora_snapshot', 'tarifa_extra_snapshot', 'facturacion_snapshot', 'coste_snapshot']);
        });
    }
};
