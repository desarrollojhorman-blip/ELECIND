<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('configuracion_empresa', 'empresa');
        Schema::rename('empresas_clientes', 'clientes');
    }

    public function down(): void
    {
        Schema::rename('empresa', 'configuracion_empresa');
        Schema::rename('clientes', 'empresas_clientes');
    }
};
