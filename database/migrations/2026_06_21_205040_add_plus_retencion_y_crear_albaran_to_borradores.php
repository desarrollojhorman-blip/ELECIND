<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borradores', function (Blueprint $table): void {
            $table->boolean('tiene_plus_retencion')->default(false)->after('observaciones');
            $table->boolean('crear_albaran')->default(false)->after('tiene_plus_retencion');
        });
    }

    public function down(): void
    {
        Schema::table('borradores', function (Blueprint $table): void {
            $table->dropColumn(['tiene_plus_retencion', 'crear_albaran']);
        });
    }
};
