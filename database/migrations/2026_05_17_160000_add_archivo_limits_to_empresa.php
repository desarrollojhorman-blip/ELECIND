<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            $table->unsignedTinyInteger('archivo_tamano_max_mb')->default(10)->after('token_caducidad_dias');
            $table->unsignedTinyInteger('archivo_cantidad_max')->default(20)->after('archivo_tamano_max_mb');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            $table->dropColumn(['archivo_tamano_max_mb', 'archivo_cantidad_max']);
        });
    }
};
