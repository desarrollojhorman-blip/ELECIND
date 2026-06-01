<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            // Si el rol es "externo": los usuarios con este rol son del cliente
            // (no del personal interno). Se usa para derivar tipo_usuario
            // automáticamente y para decidir si el form pide cliente_id (1 cliente).
            $table->boolean('es_externo')->default(false)->after('solo_clientes_asignados');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('es_externo');
        });
    }
};
