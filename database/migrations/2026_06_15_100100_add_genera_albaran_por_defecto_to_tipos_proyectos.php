<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca por tipo de proyecto si por defecto los partes creados contra él
 * generan albarán (con firma) o se quedan como partes sin firmar.
 *
 * Default true: la mayoría de tipos generan albarán (OBRAS, etc.).
 * El cliente puede ponerlo a false en tipos como MANTENIMIENTO.
 * El operario puede sobrescribir al crear cada parte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_proyectos', function (Blueprint $table): void {
            $table->boolean('genera_albaran_por_defecto')->default(true)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('tipos_proyectos', function (Blueprint $table): void {
            $table->dropColumn('genera_albaran_por_defecto');
        });
    }
};
