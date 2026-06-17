<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina la columna `es_albaran` de `partes`.
 *
 * Motivo: el estado "este parte tiene albarán generado" se deriva ahora
 * directamente de `albaran_id IS NOT NULL`. Mantener ambos campos llevaba
 * a posibles inconsistencias (flag vs FK).
 *
 * Seguro de aplicar: producción aún no tiene partes reales (Fase 4 en
 * desarrollo). En local solo había uno de smoke test que ya se borró.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partes', function (Blueprint $table): void {
            // Eliminar el índice compuesto que la usa antes de tirar la columna.
            $table->dropIndex(['estado', 'es_albaran']);
            $table->dropColumn('es_albaran');

            // Reindexar solo por estado (los filtros del Index lo usan).
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::table('partes', function (Blueprint $table): void {
            $table->dropIndex(['estado']);
            $table->boolean('es_albaran')->default(false)->after('hora_fin');
            $table->index(['estado', 'es_albaran']);
        });
    }
};
