<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Añadir nueva columna string (nullable para poder rellenarla)
        if (! Schema::hasColumn('clientes', 'codigo_cliente')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->string('codigo_cliente', 50)->nullable()->after('id');
            });
        }

        // 2. Copiar los valores enteros como string (opción B: dejarlos tal cual)
        if (Schema::hasColumn('clientes', 'numero_cliente')) {
            DB::statement('UPDATE clientes SET codigo_cliente = CAST(numero_cliente AS CHAR) WHERE codigo_cliente IS NULL');
        }

        // 3. Eliminar constraint unique y columna antigua
        // El nombre del índice conserva el nombre original de la tabla (empresas_clientes)
        if (Schema::hasColumn('clientes', 'numero_cliente')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->dropUnique('empresas_clientes_numero_cliente_unique');
                $table->dropColumn('numero_cliente');
            });
        }

        // 4. Hacer NOT NULL y añadir unique al nuevo campo
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('codigo_cliente', 50)->nullable(false)->change();
        });

        $indexes = collect(DB::select('SHOW INDEX FROM clientes'))->pluck('Key_name')->unique();
        if (! $indexes->contains('clientes_codigo_cliente_unique')) {
            Schema::table('clientes', function (Blueprint $table) {
                $table->unique('codigo_cliente');
            });
        }
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedInteger('numero_cliente')->nullable()->after('id');
        });

        DB::statement('UPDATE clientes SET numero_cliente = CAST(codigo_cliente AS UNSIGNED)');

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_codigo_cliente_unique'); // este sí lo habrá creado Laravel con el nuevo nombre
            $table->dropColumn('codigo_cliente');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedInteger('numero_cliente')->nullable(false)->change();
            $table->unique('numero_cliente');
        });
    }
};
