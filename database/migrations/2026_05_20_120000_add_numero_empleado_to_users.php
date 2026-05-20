<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Texto libre, no único, opcional. Para que cada empresa lo nombre
            // como quiera (EMP-001, 5234, etc.). Solo información HR-side.
            $table->string('numero_empleado', 50)->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('numero_empleado');
        });
    }
};
