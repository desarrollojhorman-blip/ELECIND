<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El CIF de cliente puede repetirse (decisión de negocio): se elimina el
     * índice único `clientes.cif`. Se detecta el índice por columna (no por
     * nombre, que conserva el prefijo histórico `empresas_clientes_`) usando
     * la API agnóstica de Schema (MySQL/SQLite), sin SQL crudo.
     */
    public function up(): void
    {
        $indice = collect(Schema::getIndexes('clientes'))
            ->first(fn (array $i): bool => ($i['unique'] ?? false)
                && ($i['columns'] ?? []) === ['cif']);

        if ($indice !== null) {
            Schema::table('clientes', function (Blueprint $table) use ($indice): void {
                $table->dropUnique($indice['name']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table): void {
            $table->unique('cif');
        });
    }
};
