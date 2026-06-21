<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borradores', function (Blueprint $table) {
            $table->foreignId('convertido_a_parte_id')->nullable()->nullOnDelete()->constrained('partes')->after('convertido_a_albaran_id');
        });
    }

    public function down(): void
    {
        Schema::table('borradores', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Parte::class, 'convertido_a_parte_id');
            $table->dropColumn('convertido_a_parte_id');
        });
    }
};
