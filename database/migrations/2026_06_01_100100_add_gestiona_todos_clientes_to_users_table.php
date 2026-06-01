<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Solo aplica a usuarios con rol que tenga solo_clientes_asignados
            // (p. ej. jefe_de_equipo). Si está activo: ve TODOS los clientes,
            // presentes y futuros, sin necesidad de mantener la lista a mano.
            $table->boolean('gestiona_todos_clientes')->default(false)->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('gestiona_todos_clientes');
        });
    }
};
