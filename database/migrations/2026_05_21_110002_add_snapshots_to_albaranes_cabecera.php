<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshots de las relaciones en la cabecera del albarán.
 *
 * Congela en el momento de la creación/edición:
 *   - Cliente (código, nombre, CIF).
 *   - Proyecto (código, nombre).
 *   - Concepto (nombre).
 *   - Creador y Responsable (username, nombre, apellidos, nº empleado).
 *
 * Los FKs (`cliente_id`, `proyecto_id`, `concepto_id`, `creado_por`,
 * `responsable_id`) se mantienen para informes/agrupaciones/integraciones.
 *
 * Nota: la columna `snapshot_data` JSON ya existente en `albaranes` se deja
 * intacta (puede usarse para otro fin de la app). Estos snapshots tipados
 * conviven al lado por claridad y queryabilidad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            // Cliente
            $table->integer('cliente_codigo_snapshot')->nullable()->after('cliente_id');
            $table->string('cliente_nombre_snapshot', 150)->nullable()->after('cliente_codigo_snapshot');
            $table->string('cliente_cif_snapshot', 20)->nullable()->after('cliente_nombre_snapshot');

            // Proyecto
            $table->string('proyecto_codigo_snapshot', 30)->nullable()->after('proyecto_id');
            $table->string('proyecto_nombre_snapshot', 150)->nullable()->after('proyecto_codigo_snapshot');

            // Concepto
            $table->string('concepto_nombre_snapshot', 150)->nullable()->after('concepto_id');

            // Creador
            $table->string('creador_username_snapshot', 50)->nullable()->after('creado_por');
            $table->string('creador_nombre_snapshot', 100)->nullable()->after('creador_username_snapshot');
            $table->string('creador_apellidos_snapshot', 150)->nullable()->after('creador_nombre_snapshot');
            $table->string('creador_numero_empleado_snapshot', 50)->nullable()->after('creador_apellidos_snapshot');

            // Responsable
            $table->string('responsable_username_snapshot', 50)->nullable()->after('responsable_id');
            $table->string('responsable_nombre_snapshot', 100)->nullable()->after('responsable_username_snapshot');
            $table->string('responsable_apellidos_snapshot', 150)->nullable()->after('responsable_nombre_snapshot');
            $table->string('responsable_numero_empleado_snapshot', 50)->nullable()->after('responsable_apellidos_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn([
                'cliente_codigo_snapshot',
                'cliente_nombre_snapshot',
                'cliente_cif_snapshot',
                'proyecto_codigo_snapshot',
                'proyecto_nombre_snapshot',
                'concepto_nombre_snapshot',
                'creador_username_snapshot',
                'creador_nombre_snapshot',
                'creador_apellidos_snapshot',
                'creador_numero_empleado_snapshot',
                'responsable_username_snapshot',
                'responsable_nombre_snapshot',
                'responsable_apellidos_snapshot',
                'responsable_numero_empleado_snapshot',
            ]);
        });
    }
};
