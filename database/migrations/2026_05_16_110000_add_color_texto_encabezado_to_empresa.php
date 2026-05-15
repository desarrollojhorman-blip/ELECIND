<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            if (! Schema::hasColumn('empresa', 'color_texto_encabezado')) {
                $table->string('color_texto_encabezado', 7)->default('#ffffff')->after('color_secundario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            if (Schema::hasColumn('empresa', 'color_texto_encabezado')) {
                $table->dropColumn('color_texto_encabezado');
            }
        });
    }
};
