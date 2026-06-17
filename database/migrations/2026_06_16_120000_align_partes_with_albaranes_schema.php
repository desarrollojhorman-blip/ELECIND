<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alinea el esquema de `partes` al de `albaranes` (decisión v2: el parte es
 * una copia del albarán, sin firmas ni archivos).
 *
 * Cambios:
 *  - Renombra `codigo` → `numero` y `user_id` → `creado_por`.
 *  - Quita columnas que no aplican (`hora_inicio`, `hora_fin`, snapshots de
 *    cliente/tipo_proyecto derivados, `operario_nombre_snapshot`).
 *  - Añade las columnas/snapshots que sí tiene el albarán: `cliente_id` +
 *    snapshots, `concepto_id` + snapshot, creador/responsable + snapshots,
 *    `tipo_hora`, modo "parte personalizado" con textos libres,
 *    `snapshot_data`.
 *
 * Seguro de aplicar: en producción/local no hay partes reales aún.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Drop columnas obsoletas y de relaciones derivadas.
        Schema::table('partes', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['fecha', 'user_id']);
            $table->dropIndex(['cliente_id_snapshot']);
            $table->dropColumn([
                'hora_inicio',
                'hora_fin',
                'user_id',
                'cliente_id_snapshot',
                'cliente_nombre_snapshot',
                'tipo_proyecto_id_snapshot',
                'tipo_proyecto_nombre_snapshot',
                'operario_nombre_snapshot',
            ]);
        });

        // 2) Renombrar `codigo` → `numero` (consistente con albaranes).
        Schema::table('partes', function (Blueprint $table): void {
            $table->renameColumn('codigo', 'numero');
        });

        // 3) Añadir todas las columnas que tiene `albaranes` y faltaban.
        Schema::table('partes', function (Blueprint $table): void {
            // FK + snapshots cliente
            $table->foreignId('cliente_id')->nullable()->after('numero')
                ->constrained('clientes')->nullOnDelete();
            $table->string('cliente_codigo_snapshot')->nullable()->after('cliente_id');
            $table->string('cliente_nombre_snapshot')->nullable()->after('cliente_codigo_snapshot');
            $table->string('cliente_cif_snapshot')->nullable()->after('cliente_nombre_snapshot');

            // FK + snapshot concepto
            $table->foreignId('concepto_id')->nullable()->after('proyecto_nombre_snapshot')
                ->constrained('conceptos')->nullOnDelete();
            $table->string('concepto_nombre_snapshot')->nullable()->after('concepto_id');

            // FK + snapshots creador
            $table->foreignId('creado_por')->nullable()->after('concepto_nombre_snapshot')
                ->constrained('users')->nullOnDelete();
            $table->string('creador_username_snapshot')->nullable()->after('creado_por');
            $table->string('creador_nombre_snapshot')->nullable()->after('creador_username_snapshot');
            $table->string('creador_apellidos_snapshot')->nullable()->after('creador_nombre_snapshot');
            $table->string('creador_numero_empleado_snapshot')->nullable()->after('creador_apellidos_snapshot');

            // FK + snapshots responsable
            $table->foreignId('responsable_id')->nullable()->after('creador_numero_empleado_snapshot')
                ->constrained('users')->nullOnDelete();
            $table->string('responsable_username_snapshot')->nullable()->after('responsable_id');
            $table->string('responsable_nombre_snapshot')->nullable()->after('responsable_username_snapshot');
            $table->string('responsable_apellidos_snapshot')->nullable()->after('responsable_nombre_snapshot');
            $table->string('responsable_numero_empleado_snapshot')->nullable()->after('responsable_apellidos_snapshot');

            // Tipo de jornada (enum mismo formato que albaranes)
            $table->string('tipo_hora', 30)->default('laboral')->after('responsable_numero_empleado_snapshot');

            // Modo "parte personalizado" — textos libres (igual que albaranes)
            $table->boolean('es_personalizado')->default(false)->after('observaciones');
            $table->string('cliente_texto')->nullable()->after('es_personalizado');
            $table->string('proyecto_texto')->nullable()->after('cliente_texto');
            $table->string('concepto_texto')->nullable()->after('proyecto_texto');
            $table->string('responsable_texto')->nullable()->after('concepto_texto');

            // Snapshot extra (JSON libre, igual que albaranes)
            $table->json('snapshot_data')->nullable()->after('responsable_texto');

            // Índice nuevo equivalente al que se quitó.
            $table->index(['fecha', 'creado_por']);
            $table->index('cliente_id');
        });

        // 4) Backfill: si hay partes ya creados en local, llenamos los nuevos
        // campos con valores razonables. El observador hará el resto al
        // siguiente save() de cada parte.
        DB::table('partes')->whereNull('tipo_hora')->update(['tipo_hora' => 'laboral']);
    }

    public function down(): void
    {
        Schema::table('partes', function (Blueprint $table): void {
            // 1) Quitar índices nuevos.
            $table->dropIndex(['fecha', 'creado_por']);
            $table->dropIndex(['cliente_id']);

            // 2) Quitar FKs antes de quitar columnas.
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['concepto_id']);
            $table->dropForeign(['creado_por']);
            $table->dropForeign(['responsable_id']);

            // 3) Quitar columnas añadidas.
            $table->dropColumn([
                'cliente_id', 'cliente_codigo_snapshot', 'cliente_nombre_snapshot', 'cliente_cif_snapshot',
                'concepto_id', 'concepto_nombre_snapshot',
                'creado_por', 'creador_username_snapshot', 'creador_nombre_snapshot',
                'creador_apellidos_snapshot', 'creador_numero_empleado_snapshot',
                'responsable_id', 'responsable_username_snapshot', 'responsable_nombre_snapshot',
                'responsable_apellidos_snapshot', 'responsable_numero_empleado_snapshot',
                'tipo_hora',
                'es_personalizado', 'cliente_texto', 'proyecto_texto', 'concepto_texto', 'responsable_texto',
                'snapshot_data',
            ]);
        });

        // 4) Renombrar back.
        Schema::table('partes', function (Blueprint $table): void {
            $table->renameColumn('numero', 'codigo');
        });

        // 5) Restaurar columnas originales que habíamos quitado.
        Schema::table('partes', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('codigo')
                ->constrained('users')->nullOnDelete();
            $table->time('hora_inicio')->nullable()->after('fecha');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->unsignedBigInteger('cliente_id_snapshot')->nullable();
            $table->string('cliente_nombre_snapshot')->nullable();
            $table->unsignedBigInteger('tipo_proyecto_id_snapshot')->nullable();
            $table->string('tipo_proyecto_nombre_snapshot')->nullable();
            $table->string('operario_nombre_snapshot')->nullable();
            $table->index(['fecha', 'user_id']);
            $table->index('cliente_id_snapshot');
        });
    }
};
