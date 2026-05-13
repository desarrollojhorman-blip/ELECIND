<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_empresa', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 150)->default('ELECIND');
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('cif', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email_contacto', 150)->nullable();
            $table->string('email_notificaciones', 150)->nullable();

            // Identidad visual
            $table->string('logo_path', 255)->nullable();
            $table->string('color_primario', 7)->default('#871f1f');
            $table->string('color_secundario', 7)->default('#f5e6e6');

            // Operativa
            $table->string('plantilla_numeracion_albaran', 60)->default('ALB-{YYYY}-{NNNN}');
            $table->unsignedSmallInteger('token_caducidad_dias')->default(7);
            $table->json('plantilla_pdf_config')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_empresa');
    }
};
