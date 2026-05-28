<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            // Nombre visible (mayúsculas, espacios, acentos). El 'name' sigue
            // siendo el identificador interno tipo slug usado en código.
            $table->string('etiqueta', 80)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropColumn('etiqueta');
        });
    }
};
