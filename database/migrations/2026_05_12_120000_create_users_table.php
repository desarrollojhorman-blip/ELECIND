<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->unique();
            $table->string('nombre');
            $table->string('apellidos')->nullable();
            $table->string('email')->nullable();
            $table->string('dni')->nullable();
            $table->string('cif')->nullable();
            $table->string('telefono')->nullable();
            $table->enum('tipo_usuario', ['interno', 'externo'])->default('interno');
            $table->unsignedBigInteger('empresa_cliente_id')->nullable();
            $table->enum('acceso', ['web', 'movil', 'ambos'])->default('web');
            $table->boolean('activo')->default(true);
            $table->json('preferencias_notificaciones')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tipo_usuario', 'acceso', 'activo']);
            $table->index('empresa_cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
