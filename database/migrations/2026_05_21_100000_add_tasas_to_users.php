<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarifas (€/hora) por usuario.
     * Aplican principalmente a trabajadores internos, pero quedan disponibles
     * para cualquier usuario. Opcionales (NULL).
     *
     * DECIMAL(8,3) → hasta 99.999,999 €/h. Sobra.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('tasa_hora', 8, 3)->nullable()->after('numero_empleado');
            $table->decimal('tasa_extra', 8, 3)->nullable()->after('tasa_hora');
            $table->decimal('tasa_festivo', 8, 3)->nullable()->after('tasa_extra');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['tasa_hora', 'tasa_extra', 'tasa_festivo']);
        });
    }
};
