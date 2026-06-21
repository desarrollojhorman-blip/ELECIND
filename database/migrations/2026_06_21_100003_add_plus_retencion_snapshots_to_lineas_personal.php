<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partes_lineas_personal', function (Blueprint $table): void {
            $table->decimal('tarifa_plus_retencion_snapshot', 8, 4)->default(0)->after('tarifa_extra_snapshot');
            $table->decimal('trabajador_tasa_plus_retencion_snapshot', 8, 3)->default(0)->after('trabajador_tasa_festivo_snapshot');
        });

        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->decimal('tarifa_plus_retencion_snapshot', 8, 4)->default(0)->after('tarifa_extra_snapshot');
            $table->decimal('trabajador_tasa_plus_retencion_snapshot', 8, 3)->default(0)->after('trabajador_tasa_festivo_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('partes_lineas_personal', function (Blueprint $table): void {
            $table->dropColumn(['tarifa_plus_retencion_snapshot', 'trabajador_tasa_plus_retencion_snapshot']);
        });

        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->dropColumn(['tarifa_plus_retencion_snapshot', 'trabajador_tasa_plus_retencion_snapshot']);
        });
    }
};
