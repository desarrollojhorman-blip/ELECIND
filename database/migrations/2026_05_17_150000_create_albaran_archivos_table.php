<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albaran_archivos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('albaran_id')->constrained('albaranes')->cascadeOnDelete();
            $table->string('nombre', 200);
            $table->string('ruta', 500);
            $table->string('nombre_original', 300);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano')->default(0);
            $table->foreignId('subido_por')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albaran_archivos');
    }
};
