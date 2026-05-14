<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->foreignId('concepto_id')
                ->nullable()
                ->after('proyecto_id')
                ->constrained('conceptos')
                ->nullOnDelete();

            $table->index('concepto_id');
        });
    }

    public function down(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropForeign(['concepto_id']);
            $table->dropIndex(['concepto_id']);
            $table->dropColumn('concepto_id');
        });
    }
};
