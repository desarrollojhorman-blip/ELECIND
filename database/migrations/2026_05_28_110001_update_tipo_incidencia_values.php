<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Los tipos anteriores (averia_equipo, seguridad, materiales, instalacion, otros)
        // no tienen equivalencia directa con los nuevos (albaran, ausencia, otro).
        // Cualquier valor antiguo que no sea ya uno de los nuevos pasa a 'otro'.
        DB::table('incidencias')
            ->whereNotIn('tipo', ['albaran', 'ausencia', 'otro'])
            ->update(['tipo' => 'otro']);
    }

    public function down(): void
    {
        // Sin posibilidad de restaurar el tipo original.
    }
};