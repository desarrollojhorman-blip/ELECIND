<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('plantilla_numeracion_pedido')->default('PED-{YYYY}-{NNNN}')->after('plantilla_numeracion_cliente');
            $table->string('plantilla_numeracion_proyecto')->default('PROY-{NNNN}')->after('plantilla_numeracion_pedido');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn(['plantilla_numeracion_pedido', 'plantilla_numeracion_proyecto']);
        });
    }
};
