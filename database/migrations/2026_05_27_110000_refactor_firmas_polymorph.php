<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Crear tokens_firma (polimórfico) ───────────────────────────────
        Schema::create('tokens_firma', function (Blueprint $table): void {
            $table->id();
            $table->string('firmable_type', 100);
            $table->unsignedBigInteger('firmable_id');
            $table->enum('tipo_firmante', ['trabajador', 'responsable']);
            $table->string('token', 80)->unique();
            $table->string('email_destino', 150);
            $table->string('nombre_destino', 150)->nullable();
            $table->timestamp('caduca_at');
            $table->timestamp('usado_at')->nullable();
            $table->timestamp('invalidado_at')->nullable();
            // Self-reference se añade después de copiar datos
            $table->unsignedBigInteger('reemplazado_por_token_id')->nullable();
            $table->foreignId('generado_por_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['firmable_type', 'firmable_id']);
            $table->index(['caduca_at', 'usado_at', 'invalidado_at']);
        });

        // ── 2. Crear firmas (polimórfico) ─────────────────────────────────────
        Schema::create('firmas', function (Blueprint $table): void {
            $table->id();
            $table->string('firmable_type', 100);
            $table->unsignedBigInteger('firmable_id');
            $table->enum('tipo', ['trabajador', 'responsable']);
            $table->foreignId('firmado_por_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            // FK a tokens_firma se añade después de copiar datos
            $table->unsignedBigInteger('token_id')->nullable();
            $table->string('firma_path', 255);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('geolocalizacion')->nullable();
            $table->timestamp('firmado_at');
            $table->timestamps();

            $table->unique(['firmable_type', 'firmable_id', 'tipo']);
            $table->index(['firmable_type', 'firmable_id']);
            $table->index('token_id');
        });

        // ── 3. Copiar datos existentes ────────────────────────────────────────
        // Primero tokens (sin self-reference)
        DB::statement("
            INSERT INTO tokens_firma
                (id, firmable_type, firmable_id, tipo_firmante, token, email_destino, nombre_destino,
                 caduca_at, usado_at, invalidado_at, generado_por_user_id, created_at, updated_at)
            SELECT
                id, 'App\\\\Models\\\\Albaran', albaran_id, tipo_firmante, token, email_destino, nombre_destino,
                caduca_at, usado_at, invalidado_at, generado_por_user_id, created_at, updated_at
            FROM albaran_tokens_firma
        ");

        // Actualizar self-reference una vez que todos los registros existen
        DB::statement("
            UPDATE tokens_firma tf
            JOIN albaran_tokens_firma atf ON atf.id = tf.id
            SET tf.reemplazado_por_token_id = atf.reemplazado_por_token_id
            WHERE atf.reemplazado_por_token_id IS NOT NULL
        ");

        // Luego firmas
        DB::statement("
            INSERT INTO firmas
                (id, firmable_type, firmable_id, tipo, firmado_por_user_id, token_id,
                 firma_path, ip, user_agent, geolocalizacion, firmado_at, created_at, updated_at)
            SELECT
                id, 'App\\\\Models\\\\Albaran', albaran_id, tipo, firmado_por_user_id, token_id,
                firma_path, ip, user_agent, geolocalizacion, firmado_at, created_at, updated_at
            FROM albaran_firmas
        ");

        // ── 4. Añadir constraints FK sobre las columnas ya populadas ──────────
        Schema::table('tokens_firma', function (Blueprint $table): void {
            $table->foreign('reemplazado_por_token_id')
                ->references('id')->on('tokens_firma')->nullOnDelete();
        });

        Schema::table('firmas', function (Blueprint $table): void {
            $table->foreign('token_id')
                ->references('id')->on('tokens_firma')->nullOnDelete();
        });

        // ── 5. Eliminar tablas antiguas (FK en albaran_firmas primero) ────────
        Schema::table('albaran_firmas', function (Blueprint $table): void {
            $table->dropForeign(['albaran_id']);
            $table->dropForeign(['firmado_por_user_id']);
            $table->dropForeign(['token_id']);
        });
        Schema::dropIfExists('albaran_firmas');

        Schema::table('albaran_tokens_firma', function (Blueprint $table): void {
            $table->dropForeign(['albaran_id']);
            $table->dropForeign(['reemplazado_por_token_id']);
            $table->dropForeign(['generado_por_user_id']);
        });
        Schema::dropIfExists('albaran_tokens_firma');

        // ── 6. Añadir campos de configuración de firma a borradores ──────────
        Schema::table('borradores', function (Blueprint $table): void {
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
        Schema::table('borradores', function (Blueprint $table): void {
            $table->dropForeign(['firma_trabajador_user_id']);
            $table->dropColumn([
                'firma_trabajador_user_id',
                'firma_trabajador_otro_nombre',
                'firma_trabajador_otro_correo',
                'firma_responsable_otro_nombre',
                'firma_responsable_otro_correo',
            ]);
        });

        Schema::table('firmas', function (Blueprint $table): void {
            $table->dropForeign(['token_id']);
        });
        Schema::dropIfExists('firmas');

        Schema::table('tokens_firma', function (Blueprint $table): void {
            $table->dropForeign(['reemplazado_por_token_id']);
        });
        Schema::dropIfExists('tokens_firma');
    }
};
