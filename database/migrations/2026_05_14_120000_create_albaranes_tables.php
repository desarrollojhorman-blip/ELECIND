<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albaranes', function (Blueprint $table): void {
            $table->id();
            $table->string('numero', 60)->unique();
            $table->date('fecha');
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['pendiente_firma', 'firmado', 'facturado'])
                ->default('pendiente_firma');
            $table->text('observaciones')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado', 'fecha']);
            $table->index('cliente_id');
            $table->index('proyecto_id');
            $table->index('creado_por');
            $table->index('responsable_id');
        });

        Schema::create('albaran_lineas_personal', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('albaran_id')->constrained('albaranes')->cascadeOnDelete();
            $table->foreignId('trabajador_id')->constrained('users')->restrictOnDelete();
            $table->enum('tipo_hora', ['laborable_normal', 'laborable_extra', 'festivo_normal', 'festivo_extra']);
            $table->decimal('horas', 5, 2);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->index('albaran_id');
            $table->index('trabajador_id');
        });

        Schema::create('albaran_lineas_material', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('albaran_id')->constrained('albaranes')->cascadeOnDelete();
            $table->foreignId('material_lote_id')->constrained('material_lotes')->restrictOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->string('observaciones', 255)->nullable();
            $table->timestamps();

            $table->index('albaran_id');
            $table->index('material_lote_id');
        });

        Schema::create('albaran_tokens_firma', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('albaran_id')->constrained('albaranes')->cascadeOnDelete();
            $table->enum('tipo_firmante', ['trabajador', 'responsable']);
            $table->string('token', 80)->unique();
            $table->string('email_destino', 150);
            $table->string('nombre_destino', 150)->nullable();
            $table->timestamp('caduca_at');
            $table->timestamp('usado_at')->nullable();
            $table->timestamp('invalidado_at')->nullable();
            $table->foreignId('reemplazado_por_token_id')->nullable()
                ->constrained('albaran_tokens_firma')->nullOnDelete();
            $table->foreignId('generado_por_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('albaran_id');
            $table->index(['caduca_at', 'usado_at', 'invalidado_at']);
        });

        Schema::create('albaran_firmas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('albaran_id')->constrained('albaranes')->cascadeOnDelete();
            $table->enum('tipo', ['trabajador', 'responsable']);
            $table->foreignId('firmado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('token_id')->nullable()
                ->constrained('albaran_tokens_firma')->nullOnDelete();
            $table->string('firma_path', 255);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('geolocalizacion')->nullable();
            $table->timestamp('firmado_at');
            $table->timestamps();

            $table->unique(['albaran_id', 'tipo']);
            $table->index('token_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('albaran_firmas');
        Schema::dropIfExists('albaran_tokens_firma');
        Schema::dropIfExists('albaran_lineas_material');
        Schema::dropIfExists('albaran_lineas_personal');
        Schema::dropIfExists('albaranes');
    }
};
