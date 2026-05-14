<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade soporte para:
 *  - Logo separado para albaranes/facturas (fallback al logo principal).
 *  - Ratio aspect (ancho/alto) detectado automáticamente al subir cada logo.
 *  - Zoom porcentual por logo (80-130) para ajustar el tamaño visual sin
 *    reprocesar la imagen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            if (! Schema::hasColumn('empresa', 'logo_ratio')) {
                $table->float('logo_ratio')->nullable()->after('logo_path');
            }
            if (! Schema::hasColumn('empresa', 'logo_zoom')) {
                $table->unsignedSmallInteger('logo_zoom')->default(100)->after('logo_ratio');
            }
            if (! Schema::hasColumn('empresa', 'logo_albaran_path')) {
                $table->string('logo_albaran_path', 255)->nullable()->after('logo_zoom');
            }
            if (! Schema::hasColumn('empresa', 'logo_albaran_ratio')) {
                $table->float('logo_albaran_ratio')->nullable()->after('logo_albaran_path');
            }
            if (! Schema::hasColumn('empresa', 'logo_albaran_zoom')) {
                $table->unsignedSmallInteger('logo_albaran_zoom')->default(100)->after('logo_albaran_ratio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            foreach (['logo_albaran_zoom', 'logo_albaran_ratio', 'logo_albaran_path', 'logo_zoom', 'logo_ratio'] as $col) {
                if (Schema::hasColumn('empresa', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
