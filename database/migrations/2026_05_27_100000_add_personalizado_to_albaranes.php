<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->boolean('es_personalizado')->default(false)->after('observaciones');
            $table->string('cliente_texto', 200)->nullable()->after('es_personalizado');
            $table->string('proyecto_texto', 200)->nullable()->after('cliente_texto');
            $table->string('concepto_texto', 200)->nullable()->after('proyecto_texto');
            $table->string('responsable_texto', 200)->nullable()->after('concepto_texto');
        });

        // Permitir cliente_id nulo para partes personalizados sin proyecto asignado
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn(['es_personalizado', 'cliente_texto', 'proyecto_texto', 'concepto_texto', 'responsable_texto']);
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
        });
    }
};
