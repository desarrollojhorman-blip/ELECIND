<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renombrar `tipo_dia` → `tipo_hora` en albaranes y ampliar a 4 valores:
 *  - laboral
 *  - laboral_noche
 *  - festivo
 *  - festivo_noche
 *
 * Antes era un enum de 2 valores (laborable/festivo) y las extras se reflejaban
 * en cada línea (`horas_extra`). Ahora el "tipo de hora" del albarán completo
 * tiene 4 valores ya integrados, uno por albarán.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadir la nueva columna con los 4 valores y un default temporal.
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->enum('tipo_hora', ['laboral', 'laboral_noche', 'festivo', 'festivo_noche'])
                ->default('laboral')
                ->after('estado');
        });

        // 2. Mapear los valores existentes de tipo_dia → tipo_hora.
        //    laborable → laboral · festivo → festivo
        DB::table('albaranes')->where('tipo_dia', 'laborable')->update(['tipo_hora' => 'laboral']);
        DB::table('albaranes')->where('tipo_dia', 'festivo')->update(['tipo_hora' => 'festivo']);

        // 3. Eliminar la columna antigua.
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn('tipo_dia');
        });
    }

    public function down(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->enum('tipo_dia', ['laborable', 'festivo'])
                ->default('laborable')
                ->after('estado');
        });

        DB::table('albaranes')->whereIn('tipo_hora', ['laboral', 'laboral_noche'])->update(['tipo_dia' => 'laborable']);
        DB::table('albaranes')->whereIn('tipo_hora', ['festivo', 'festivo_noche'])->update(['tipo_dia' => 'festivo']);

        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn('tipo_hora');
        });
    }
};
