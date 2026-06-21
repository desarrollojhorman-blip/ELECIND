<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partes', function (Blueprint $table): void {
            $table->boolean('tiene_plus_retencion')->default(false)->after('tipo_hora');
        });

        Schema::table('albaranes', function (Blueprint $table): void {
            $table->boolean('tiene_plus_retencion')->default(false)->after('tipo_hora');
        });
    }

    public function down(): void
    {
        Schema::table('partes', function (Blueprint $table): void {
            $table->dropColumn('tiene_plus_retencion');
        });

        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropColumn('tiene_plus_retencion');
        });
    }
};
