<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('logo_app_path')->nullable()->after('logo_albaran_zoom');
            $table->float('logo_app_ratio')->nullable()->after('logo_app_path');
            $table->integer('logo_app_zoom')->default(100)->after('logo_app_ratio');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropColumn(['logo_app_path', 'logo_app_ratio', 'logo_app_zoom']);
        });
    }
};
