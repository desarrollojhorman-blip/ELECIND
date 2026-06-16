<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de tipos imputables (atributos) en partes y albaranes.
 *
 * Los 11 atributos están fijados por código (seeder). No se editan desde UI.
 * Se siembran una vez con AtributosHoraSeeder.
 *
 * Tres grupos:
 *   - normal: Labor, Lab Noche, Fest, Fest Noct
 *   - extra:  Ex Lab, Ex Lab Noc, Ex Fes, Ex Fes Noct
 *   - plus:   PLUS RETEN, PLUS FESTIVO, PLUS NOCHE
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atributos_hora', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre_corto', 30);
            $table->string('nombre_largo', 80);
            $table->enum('grupo', ['normal', 'extra', 'plus']);
            $table->string('mapeo_tasa', 30)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['grupo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atributos_hora');
    }
};
