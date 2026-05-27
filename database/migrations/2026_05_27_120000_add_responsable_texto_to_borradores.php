<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borradores', function (Blueprint $table): void {
            $table->string('responsable_texto', 255)->nullable()->after('responsable_id');
        });
    }

    public function down(): void
    {
        Schema::table('borradores', function (Blueprint $table): void {
            $table->dropColumn('responsable_texto');
        });
    }
};
