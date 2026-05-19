<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reutilización de código tras borrado (soft-delete):
     *
     * - `codigo_cliente` pasa a NULLABLE: al borrar, el código se mueve a
     *   `codigo_cliente_anterior` y `codigo_cliente` queda NULL. Como un índice
     *   ÚNICO permite múltiples NULL (MySQL/SQLite), el código queda libre para
     *   reusar sin perder la garantía de unicidad entre clientes ACTIVOS.
     * - Que un cliente activo nunca tenga código vacío lo garantiza la app
     *   (regla `required` en ClienteFields, front + back).
     *
     * Detección del índice por columna (no por nombre) con la API agnóstica de
     * Schema (MySQL/SQLite), sin SQL crudo.
     */
    public function up(): void
    {
        $indice = collect(Schema::getIndexes('clientes'))
            ->first(fn (array $i): bool => ($i['unique'] ?? false)
                && ($i['columns'] ?? []) === ['codigo_cliente']);

        if ($indice !== null) {
            Schema::table('clientes', function (Blueprint $table) use ($indice): void {
                $table->dropUnique($indice['name']);
            });
        }

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unsignedInteger('codigo_cliente')->nullable()->change();
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unique('codigo_cliente');
        });

        Schema::table('clientes', function (Blueprint $table): void {
            if (! Schema::hasColumn('clientes', 'codigo_cliente_anterior')) {
                $table->unsignedInteger('codigo_cliente_anterior')
                    ->nullable()
                    ->after('codigo_cliente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropColumn('codigo_cliente_anterior');
        });

        $indice = collect(Schema::getIndexes('clientes'))
            ->first(fn (array $i): bool => ($i['unique'] ?? false)
                && ($i['columns'] ?? []) === ['codigo_cliente']);

        if ($indice !== null) {
            Schema::table('clientes', function (Blueprint $table) use ($indice): void {
                $table->dropUnique($indice['name']);
            });
        }

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unsignedInteger('codigo_cliente')->nullable(false)->change();
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unique('codigo_cliente');
        });
    }
};
