<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->unsignedInteger('codigo_secuencial')->nullable()->after('codigo');
            $table->string('codigo_borrador', 50)->nullable()->after('codigo_secuencial');
            $table->unsignedInteger('numero_borrador')->nullable()->after('codigo_borrador');
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['codigo_secuencial', 'codigo_borrador', 'numero_borrador']);
        });
    }
};
