<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->foreignId('firma_trabajador_user_id')
                  ->nullable()->after('responsable_id')
                  ->constrained('users')->nullOnDelete();
            $table->string('firma_trabajador_otro_nombre', 150)->nullable()->after('firma_trabajador_user_id');
            $table->string('firma_trabajador_otro_correo', 150)->nullable()->after('firma_trabajador_otro_nombre');
            $table->string('firma_responsable_otro_nombre', 150)->nullable()->after('firma_trabajador_otro_correo');
            $table->string('firma_responsable_otro_correo', 150)->nullable()->after('firma_responsable_otro_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('albaranes', function (Blueprint $table): void {
            $table->dropForeign(['firma_trabajador_user_id']);
            $table->dropColumn([
                'firma_trabajador_user_id',
                'firma_trabajador_otro_nombre',
                'firma_trabajador_otro_correo',
                'firma_responsable_otro_nombre',
                'firma_responsable_otro_correo',
            ]);
        });
    }
};
