<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Amplía las tasas del trabajador de 3 a 8 explícitas.
 *
 * Antes (3 tasas):
 *   - tasa_hora     → Labor
 *   - tasa_extra    → Ex Lab
 *   - tasa_festivo  → Fest
 *
 * Después (8 tasas) — añadimos 5 nuevas:
 *   - tasa_lab_noche       → Lab Noche
 *   - tasa_fest_noche      → Fest Noct
 *   - tasa_ex_lab_noc      → Ex Lab Noc
 *   - tasa_ex_fes          → Ex Fes
 *   - tasa_ex_fes_noct     → Ex Fes Noct
 *
 * Norma: NOT NULL DEFAULT 0 en todas las tasas (acuerdo v2). Las 3 existentes
 * tenían NULLs; se hace backfill a 0 antes de cambiar el tipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Backfill: las 3 tasas existentes pueden tener NULL → convertirlos a 0
        //    antes de cambiar el tipo de columna.
        DB::table('users')->whereNull('tasa_hora')->update(['tasa_hora' => 0]);
        DB::table('users')->whereNull('tasa_extra')->update(['tasa_extra' => 0]);
        DB::table('users')->whereNull('tasa_festivo')->update(['tasa_festivo' => 0]);

        // 2) Cambiar las 3 existentes a NOT NULL DEFAULT 0.
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('tasa_hora', 8, 3)->default(0)->nullable(false)->change();
            $table->decimal('tasa_extra', 8, 3)->default(0)->nullable(false)->change();
            $table->decimal('tasa_festivo', 8, 3)->default(0)->nullable(false)->change();
        });

        // 3) Añadir las 5 nuevas. NOT NULL DEFAULT 0.
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('tasa_lab_noche', 8, 3)->default(0)->after('tasa_hora');
            $table->decimal('tasa_fest_noche', 8, 3)->default(0)->after('tasa_festivo');
            $table->decimal('tasa_ex_lab_noc', 8, 3)->default(0)->after('tasa_extra');
            $table->decimal('tasa_ex_fes', 8, 3)->default(0)->after('tasa_ex_lab_noc');
            $table->decimal('tasa_ex_fes_noct', 8, 3)->default(0)->after('tasa_ex_fes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'tasa_lab_noche',
                'tasa_fest_noche',
                'tasa_ex_lab_noc',
                'tasa_ex_fes',
                'tasa_ex_fes_noct',
            ]);
        });

        // Las 3 originales se quedan como NOT NULL DEFAULT 0 — la migración previa
        // las creó nullable, pero volver a permitir NULL no aporta nada y rompería
        // el invariante de "siempre hay valor". Se mantienen así.
    }
};
