<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor de horas:
 *  - Añade `tipo_dia` (laborable/festivo) al albarán como característica del día completo.
 *  - Sustituye `tipo_hora` (combinaba día + extra) por `horas_extra` separadas en cada línea.
 *
 * Este modelo refleja mejor la realidad: un día es laborable o festivo, y dentro del día
 * cada trabajador hace X horas normales y opcionalmente Y horas extra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->enum('tipo_dia', ['laborable', 'festivo'])
                ->default('laborable')
                ->after('estado');
        });

        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->decimal('horas_extra', 5, 2)->default(0)->after('horas');
            $table->dropColumn('tipo_hora');
        });
    }

    public function down(): void
    {
        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->enum('tipo_hora', ['laborable_normal', 'laborable_extra', 'festivo_normal', 'festivo_extra'])
                ->default('laborable_normal')
                ->after('horas_extra');
            $table->dropColumn('horas_extra');
        });

        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn('tipo_dia');
        });
    }
};
