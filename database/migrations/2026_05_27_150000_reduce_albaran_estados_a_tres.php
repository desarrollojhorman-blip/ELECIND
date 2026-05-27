<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si hay albaranes en estados obsoletos, los reasignamos:
        //  - borrador  → pendiente_firma (aún no firmado)
        //  - archivado → facturado       (estado terminal previo)
        DB::table('albaranes')->where('estado', 'borrador')->update(['estado' => 'pendiente_firma']);
        DB::table('albaranes')->where('estado', 'archivado')->update(['estado' => 'facturado']);

        // Re-declarar la columna enum con solo los 3 estados válidos.
        // Usamos ALTER TABLE crudo para evitar dependencia de doctrine/dbal.
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                "ALTER TABLE albaranes MODIFY COLUMN estado "
                ."ENUM('pendiente_firma','firmado','facturado') NOT NULL DEFAULT 'pendiente_firma'"
            );
        }
        // SQLite (tests): no soporta MODIFY ENUM; el cast del modelo se encarga
        // de validar los valores y los datos antiguos ya quedaron migrados arriba.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement(
                "ALTER TABLE albaranes MODIFY COLUMN estado "
                ."ENUM('borrador','pendiente_firma','firmado','facturado','archivado') NOT NULL DEFAULT 'borrador'"
            );
        }
    }
};
