<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade columnas de snapshot económico a partes_lineas_personal.
 *
 * tarifa_hora_snapshot  → lo que se cobra al cliente por hora normal
 * tarifa_extra_snapshot → lo que se cobra al cliente por hora extra
 * facturacion_snapshot  → horas × tarifa_hora + horas_extra × tarifa_extra
 * coste_snapshot        → horas × tasa_hora   + horas_extra × tasa_extra
 *
 * Valores congelados al asignar el trabajador; editables manualmente después.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partes_lineas_personal', function (Blueprint $table): void {
            $table->decimal('tarifa_hora_snapshot', 8, 4)->nullable()->after('trabajador_tasa_festivo_snapshot');
            $table->decimal('tarifa_extra_snapshot', 8, 4)->nullable()->after('tarifa_hora_snapshot');
            $table->decimal('facturacion_snapshot', 10, 2)->nullable()->after('tarifa_extra_snapshot');
            $table->decimal('coste_snapshot', 10, 2)->nullable()->after('facturacion_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('partes_lineas_personal', function (Blueprint $table): void {
            $table->dropColumn(['tarifa_hora_snapshot', 'tarifa_extra_snapshot', 'facturacion_snapshot', 'coste_snapshot']);
        });
    }
};
