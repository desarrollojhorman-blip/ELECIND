<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar datos antes de alterar el enum
        DB::statement("UPDATE proyectos SET estado = 'activo'  WHERE estado = 'borrador'");
        DB::statement("UPDATE proyectos SET estado = 'cerrado' WHERE estado = 'archivado'");

        DB::statement("ALTER TABLE proyectos MODIFY COLUMN estado ENUM('activo','inactivo','cerrado') NOT NULL DEFAULT 'activo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE proyectos MODIFY COLUMN estado ENUM('borrador','activo','cerrado','archivado') NOT NULL DEFAULT 'borrador'");
    }
};
