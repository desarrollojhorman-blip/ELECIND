<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('empresa_cliente_id', 'cliente_id');
        });

        Schema::table('proyectos', function (Blueprint $table): void {
            $table->renameColumn('empresa_cliente_id', 'cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('cliente_id', 'empresa_cliente_id');
        });

        Schema::table('proyectos', function (Blueprint $table): void {
            $table->renameColumn('cliente_id', 'empresa_cliente_id');
        });
    }
};
