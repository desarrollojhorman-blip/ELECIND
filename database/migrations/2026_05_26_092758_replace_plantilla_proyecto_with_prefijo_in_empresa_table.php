<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn('plantilla_numeracion_proyecto');
            $table->string('prefijo_proyecto', 10)->default('PR')->after('plantilla_numeracion_albaran');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn('prefijo_proyecto');
            $table->string('plantilla_numeracion_proyecto')->default('PROY-{NNNN}');
        });
    }
};
