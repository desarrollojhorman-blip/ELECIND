<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eliminar la columna `observaciones` de las dos tablas de líneas de albarán
 * (personal y material). El albarán ya tiene un campo `observaciones` global
 * en su cabecera; las observaciones por línea eran redundantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('albaran_lineas_personal', 'observaciones')) {
            Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
                $table->dropColumn('observaciones');
            });
        }

        if (Schema::hasColumn('albaran_lineas_material', 'observaciones')) {
            Schema::table('albaran_lineas_material', function (Blueprint $table): void {
                $table->dropColumn('observaciones');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('albaran_lineas_personal', 'observaciones')) {
            Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
                $table->string('observaciones', 255)->nullable()->after('horas_extra');
            });
        }

        if (! Schema::hasColumn('albaran_lineas_material', 'observaciones')) {
            Schema::table('albaran_lineas_material', function (Blueprint $table): void {
                $table->string('observaciones', 255)->nullable()->after('cantidad');
            });
        }
    }
};
