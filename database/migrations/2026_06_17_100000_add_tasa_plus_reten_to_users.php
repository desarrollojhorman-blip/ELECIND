<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade la tasa de Plus Retén al trabajador.
 *
 * Decisión 17/06/2026: tanto cliente como trabajador manejan UN solo plus, el
 * de retén. En el lado trabajador su coste es un importe por trabajador
 * (editable en Tarifas → Trabajadores), igual que el resto de tasas.
 *
 * NOT NULL DEFAULT 0 (mismo invariante que las otras 8 tasas). Aditiva.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('tasa_plus_reten', 8, 3)->default(0)->after('tasa_ex_fes_noct');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('tasa_plus_reten');
        });
    }
};
