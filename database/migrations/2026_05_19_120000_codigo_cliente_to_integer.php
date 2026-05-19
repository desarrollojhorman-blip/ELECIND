<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * codigo_cliente pasa a ser un entero único (solo números).
     *
     * Backfill A1: los códigos no numéricos (o que colisionarían al castear a
     * entero) se reasignan al siguiente número libre, sin perder la fila.
     * Seguro en producción y agnóstico de driver (MySQL/SQLite) — sin SQL crudo.
     */
    public function up(): void
    {
        // 1. Backfill: dejar todos los codigo_cliente como enteros únicos.
        $rows = DB::table('clientes')->orderBy('id')->get(['id', 'codigo_cliente']);

        $maxInt = 0;
        foreach ($rows as $row) {
            $valor = (string) $row->codigo_cliente;
            if ($valor !== '' && ctype_digit($valor)) {
                $maxInt = max($maxInt, (int) $valor);
            }
        }

        $siguiente = $maxInt + 1;
        $vistos = [];

        foreach ($rows as $row) {
            $valor = (string) $row->codigo_cliente;

            if ($valor !== '' && ctype_digit($valor) && ! isset($vistos[(int) $valor])) {
                $final = (int) $valor;
            } else {
                $final = $siguiente;
                $siguiente++;
            }

            $vistos[$final] = true;

            if ((string) $final !== $valor) {
                DB::table('clientes')->where('id', $row->id)->update(['codigo_cliente' => $final]);
            }
        }

        // 2. Cambiar el tipo de columna a entero sin signo (manteniendo único).
        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropUnique(['codigo_cliente']);
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unsignedInteger('codigo_cliente')->nullable(false)->change();
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unique('codigo_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table): void {
            $table->dropUnique(['codigo_cliente']);
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->string('codigo_cliente', 50)->nullable(false)->change();
        });

        Schema::table('clientes', function (Blueprint $table): void {
            $table->unique('codigo_cliente');
        });
    }
};
