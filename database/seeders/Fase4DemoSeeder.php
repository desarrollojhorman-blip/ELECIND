<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

class Fase4DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Crear / asegurar usuarios demo ───────────────────────────────
        // Restaurar si fueron soft-deleted en ejecución anterior
        User::withTrashed()->whereIn('username', ['Jorge10', 'JesúsLop', 'Patricia20'])->restore();

        $rolTrabajador = Role::where('name', 'trabajador')->firstOrFail();

        $jorge = User::updateOrCreate(
            ['username' => 'Jorge10'],
            ['nombre' => 'Jorge', 'apellidos' => 'Martínez Ruiz', 'tipo_usuario' => 'interno', 'activo' => true, 'password' => Hash::make('password')]
        );
        $jorge->syncRoles([$rolTrabajador]);

        $jesus = User::updateOrCreate(
            ['username' => 'JesúsLop'],
            ['nombre' => 'Jesús', 'apellidos' => 'López García', 'tipo_usuario' => 'interno', 'activo' => true, 'password' => Hash::make('password')]
        );
        $jesus->syncRoles([$rolTrabajador]);

        $patricia = User::updateOrCreate(
            ['username' => 'Patricia20'],
            ['nombre' => 'Patricia', 'apellidos' => 'Sánchez Moreno', 'tipo_usuario' => 'interno', 'activo' => true, 'password' => Hash::make('password')]
        );
        $patricia->syncRoles([$rolTrabajador]);

        // Limpiar logs anteriores de estos usuarios para poder regenerarlos limpios
        Activity::whereIn('causer_id', [$jorge->id, $jesus->id, $patricia->id])
            ->where('causer_type', User::class)
            ->delete();

        // ── 2. Log configuración empresa — EL MÁS ANTIGUO (17/07/2025) ─────
        if (! Activity::where('subject_type', Empresa::class)->whereDate('created_at', '2025-07-17')->exists()) {
            $empresa    = Empresa::first();
            $superadmin = User::whereHas('roles', fn ($q) => $q->where('name', 'superadmin'))->first();

            if ($empresa && $superadmin) {
                Activity::create([
                    'log_name'     => 'empresa',
                    'description'  => 'updated',
                    'event'        => 'updated',
                    'subject_type' => Empresa::class,
                    'subject_id'   => $empresa->id,
                    'causer_type'  => User::class,
                    'causer_id'    => $superadmin->id,
                    'properties'   => [
                        'attributes' => [
                            'nombre' => 'ELECIND', 'razon_social' => 'ELECTRICIDAD INDUSTRIAL E INSTALACIONES, S.L.L.',
                            'direccion' => 'Pol. Ind. La Solana - Calle Ebanistas, Parcela A4',
                            'codigo_postal' => '13240', 'poblacion' => 'La Solana', 'provincia' => 'Ciudad Real',
                            'telefono' => '926095015', 'movil' => '620284897',
                            'web' => 'www.elecind.com', 'email_notificaciones' => 'elecind@live.com',
                        ],
                        'old' => [
                            'nombre' => 'ENIA', 'razon_social' => null, 'direccion' => null,
                            'codigo_postal' => null, 'poblacion' => null, 'provincia' => null,
                            'telefono' => null, 'movil' => null, 'web' => null, 'email_notificaciones' => null,
                        ],
                        'ip' => '81.0.52.129',
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/137.0.0.0 Safari/537.36',
                    ],
                    'created_at' => '2025-07-17 10:32:00',
                    'updated_at' => '2025-07-17 10:32:00',
                ]);
            }
        }

        // ── 3. Logs exactos de la imagen (septiembre 2025) ──────────────────
        // Jorge10 — 09/09/2025 11:30 — entró y generó albarán
        $this->log($jorge, 'login',   '81.0.52.130', '2025-09-09 11:30:00');
        $this->log($jorge, 'logout',  '81.0.52.130', '2025-09-09 13:15:00');

        // JesúsLop — 10/09/2025 12:37 — registro en app
        $this->log($jesus, 'login',   '81.0.52.131', '2025-09-10 12:37:00');
        $this->log($jesus, 'logout',  '81.0.52.131', '2025-09-10 14:20:00');

        // Patricia20 — 11/09/2025 15:28 login y 15:31 logout (sesión muy corta)
        $this->log($patricia, 'login',  '81.0.52.132', '2025-09-11 15:28:00');
        $this->log($patricia, 'logout', '81.0.52.132', '2025-09-11 15:31:00');

        // ── 4. Logs recurrentes desde 15/09/2025 hasta hoy ──────────────────
        // (Los usuarios se eliminan al final del seeder — los logs quedan huérfanos
        //  de forma intencionada: "existieron y fueron dados de baja")
        // Cada usuario tiene su día fijo de conexión semanal y viene cada 1-2 semanas.
        //   Jorge    → Miércoles, semanas pares
        //   JesúsLop → Martes,    todas las semanas menos cada 3ª
        //   Patricia → Jueves,    semanas impares

        $configs = [
            [
                'user'    => $jorge,
                'ip'      => '81.0.52.130',
                'ua'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/137.0.0.0 Safari/537.36',
                'weekday' => Carbon::WEDNESDAY,
                'viene'   => fn (int $w): bool => $w % 2 === 0,
                'horas'   => [8, 9, 10, 11, 14, 15],
            ],
            [
                'user'    => $jesus,
                'ip'      => '81.0.52.131',
                'ua'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/136.0.0.0 Safari/537.36',
                'weekday' => Carbon::TUESDAY,
                'viene'   => fn (int $w): bool => $w % 3 !== 2,
                'horas'   => [9, 10, 12, 13, 15, 16],
            ],
            [
                'user'    => $patricia,
                'ip'      => '81.0.52.132',
                'ua'      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1',
                'weekday' => Carbon::THURSDAY,
                'viene'   => fn (int $w): bool => $w % 2 === 1,
                'horas'   => [8, 10, 11, 14, 16, 17],
            ],
        ];

        $inicio = Carbon::parse('2025-09-15');
        $fin    = Carbon::now()->startOfDay();

        foreach ($configs as $ui => $cfg) {
            $semana = $inicio->copy()->startOfWeek(Carbon::MONDAY);
            $w      = 0;

            while ($semana->lte($fin)) {
                if (($cfg['viene'])($w)) {
                    $dia = $semana->copy()->next($cfg['weekday']);

                    if ($dia->between($inicio, $fin)) {
                        $horas = $cfg['horas'];
                        $hora  = $horas[($w + $ui) % count($horas)];
                        $min   = ($w * 13 + $ui * 7) % 60;

                        $entrada = $dia->copy()->setTime($hora, $min);
                        $salida  = $entrada->copy()->addMinutes(30 + (($w * 11 + $ui * 17) % 120));

                        $this->log($cfg['user'], 'login',  $cfg['ip'], $entrada->toDateTimeString(), $cfg['ua']);
                        $this->log($cfg['user'], 'logout', $cfg['ip'], $salida->toDateTimeString(),  $cfg['ua']);
                    }
                }

                $semana->addWeek();
                $w++;
            }
        }

        // ── 5. Eliminar los usuarios ficticios — los logs quedan como rastro ──
        // Soft-delete: los logs mantienen el causer_id pero los usuarios no aparecen
        // en el listado. Narrativa: "existieron y fueron dados de baja".
        User::whereIn('username', ['Jorge10', 'JesúsLop', 'Patricia20'])->delete();
    }

    private function log(User $user, string $tipo, string $ip, string $fechaHora, string $ua = ''): void
    {
        Activity::create([
            'log_name'    => 'default',
            'description' => $tipo,
            'event'       => null,
            'causer_type' => User::class,
            'causer_id'   => $user->id,
            'properties'  => array_filter(['ip' => $ip, 'user_agent' => $ua ?: null]),
            'created_at'  => $fechaHora,
            'updated_at'  => $fechaHora,
        ]);
    }
}
