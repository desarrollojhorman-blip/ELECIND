<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('empresas_clientes', 'numero_cliente')) {
            return;
        }

        Schema::table('empresas_clientes', function (Blueprint $table): void {
            $table->unsignedInteger('numero_cliente')->nullable()->after('id');
        });

        $rows = DB::table('empresas_clientes')
            ->select('id')
            ->orderBy('id')
            ->get();

        $next = 1;
        foreach ($rows as $row) {
            DB::table('empresas_clientes')
                ->where('id', $row->id)
                ->update(['numero_cliente' => $next]);
            $next++;
        }

        Schema::table('empresas_clientes', function (Blueprint $table): void {
            $table->unsignedInteger('numero_cliente')->nullable(false)->change();
            $table->unique('numero_cliente', 'empresas_clientes_numero_cliente_unique');
        });

        if (Schema::hasColumn('empresas_clientes', 'correo_notificaciones')) {
            Schema::table('empresas_clientes', function (Blueprint $table): void {
                $table->dropColumn('correo_notificaciones');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('empresas_clientes', 'numero_cliente')) {
            return;
        }

        if (! Schema::hasColumn('empresas_clientes', 'correo_notificaciones')) {
            Schema::table('empresas_clientes', function (Blueprint $table): void {
                $table->string('correo_notificaciones')->nullable()->after('email');
            });
        }

        Schema::table('empresas_clientes', function (Blueprint $table): void {
            $table->dropUnique('empresas_clientes_numero_cliente_unique');
            $table->dropColumn('numero_cliente');
        });
    }
};
