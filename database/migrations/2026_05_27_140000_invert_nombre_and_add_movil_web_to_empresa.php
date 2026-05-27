<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Renombrar: nombre (largo, razón social) -> razon_social
        //               nombre_comercial (corto, marca) -> nombre
        // CHANGE COLUMN preserva los datos de la columna y solo cambia nombre/tipo.
        // Se hace en dos pasos con un nombre temporal para evitar choque de nombres.
        DB::statement('ALTER TABLE empresa CHANGE COLUMN nombre razon_social VARCHAR(150) NULL');
        DB::statement('ALTER TABLE empresa CHANGE COLUMN nombre_comercial nombre VARCHAR(150) NULL');

        // 2) Añadir nuevas columnas de contacto
        Schema::table('empresa', function (Blueprint $table): void {
            $table->string('movil', 30)->nullable()->after('telefono');
            $table->string('web', 255)->nullable()->after('movil');
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table): void {
            $table->dropColumn(['movil', 'web']);
        });

        // Invertir el rename. El default original de `nombre` era 'ELECIND' NOT NULL.
        DB::statement('ALTER TABLE empresa CHANGE COLUMN nombre nombre_comercial VARCHAR(150) NULL');
        DB::statement("ALTER TABLE empresa CHANGE COLUMN razon_social nombre VARCHAR(150) NOT NULL DEFAULT 'ELECIND'");
    }
};
