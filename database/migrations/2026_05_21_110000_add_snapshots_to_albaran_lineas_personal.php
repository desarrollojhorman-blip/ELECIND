<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshots del trabajador en cada línea de personal del albarán.
 *
 * Objetivo: congelar los datos del trabajador (nombre, apellidos, nº empleado
 * y las 3 tasas €/hora) en el momento en que se asocia al albarán, de modo que
 * los albaranes históricos sean inmutables aunque el `users.*` cambie luego.
 *
 * FK `trabajador_id` se mantiene intacta. Estos campos son ADICIONALES.
 * Todas NULL para no romper los albaranes existentes; la lógica de relleno
 * vendrá en otra iteración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->string('trabajador_nombre_snapshot', 100)->nullable()->after('horas_extra');
            $table->string('trabajador_apellidos_snapshot', 150)->nullable()->after('trabajador_nombre_snapshot');
            $table->string('trabajador_numero_empleado_snapshot', 50)->nullable()->after('trabajador_apellidos_snapshot');
            $table->decimal('trabajador_tasa_hora_snapshot', 8, 3)->nullable()->after('trabajador_numero_empleado_snapshot');
            $table->decimal('trabajador_tasa_extra_snapshot', 8, 3)->nullable()->after('trabajador_tasa_hora_snapshot');
            $table->decimal('trabajador_tasa_festivo_snapshot', 8, 3)->nullable()->after('trabajador_tasa_extra_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('albaran_lineas_personal', function (Blueprint $table): void {
            $table->dropColumn([
                'trabajador_nombre_snapshot',
                'trabajador_apellidos_snapshot',
                'trabajador_numero_empleado_snapshot',
                'trabajador_tasa_hora_snapshot',
                'trabajador_tasa_extra_snapshot',
                'trabajador_tasa_festivo_snapshot',
            ]);
        });
    }
};
