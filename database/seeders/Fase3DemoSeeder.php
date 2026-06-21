<?php

namespace Database\Seeders;

use App\Enums\EstadoAusencia;
use App\Enums\EstadoIncidencia;
use App\Enums\PrioridadIncidencia;
use App\Enums\TipoAusencia;
use App\Enums\TipoIncidencia;
use App\Models\Ausencia;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\App;
use Illuminate\Support\Carbon;

class Fase3DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->command?->warn('Fase3DemoSeeder omitido: entorno no es local/development/testing.');
            return;
        }

        $trabajadores = User::role('trabajador')->get();
        if ($trabajadores->isEmpty()) {
            $this->command?->warn('Fase3DemoSeeder omitido: no hay trabajadores. Ejecuta Fase1DemoSeeder primero.');
            return;
        }

        $aprobador = User::role('administrador')->first()
            ?? User::role('superadmin')->first()
            ?? $trabajadores->first();

        $this->command?->info('Sembrando datos de demo Fase 3 (ausencias e incidencias)…');

        // ── Ausencias ───────────────────────────────────────────────────
        $this->command?->info('  · Creando ausencias…');

        $tiposAusencia = TipoAusencia::cases();
        $faker = fake('es_ES');

        foreach ($trabajadores as $trabajador) {
            // Cada trabajador tiene entre 2 y 5 ausencias
            $cantidad = $faker->numberBetween(2, 5);
            for ($i = 0; $i < $cantidad; $i++) {
                $inicio = Carbon::parse($faker->dateTimeBetween('-8 months', 'now'));
                $dias   = $faker->numberBetween(1, 14);
                $fin    = $inicio->copy()->addDays($dias);
                $estado = $faker->randomElement([
                    EstadoAusencia::PENDIENTE,
                    EstadoAusencia::APROBADA,
                    EstadoAusencia::APROBADA,
                    EstadoAusencia::APROBADA,
                    EstadoAusencia::RECHAZADA,
                ]);

                Ausencia::create([
                    'trabajador_id'  => $trabajador->id,
                    'tipo'           => $faker->randomElement($tiposAusencia),
                    'fecha_inicio'   => $inicio->format('Y-m-d'),
                    'fecha_fin'      => $fin->format('Y-m-d'),
                    'estado'         => $estado,
                    'motivo'         => $faker->sentence(10),
                    'observaciones'  => $faker->boolean(25)
                        ? ($estado === EstadoAusencia::RECHAZADA ? 'No cumple los requisitos del convenio. '.$faker->sentence(6) : $faker->sentence(8))
                        : null,
                    'aprobado_por'   => $estado !== EstadoAusencia::PENDIENTE ? $aprobador->id : null,
                    'aprobado_at'    => $estado !== EstadoAusencia::PENDIENTE ? $inicio->copy()->subDays($faker->numberBetween(1, 5))->setTime(12, 0, 0) : null,
                ]);
            }
        }

        // ── Incidencias ─────────────────────────────────────────────────
        $this->command?->info('  · Creando incidencias…');

        $titulos = [
            'Avería en equipo de medición',
            'Fallo en cuadro eléctrico',
            'Cortocircuito en línea de distribución',
            'Problema con herramienta de corte',
            'Incidencia de seguridad en obra',
            'Material defectuoso recibido',
            'Rotura de cable de alimentación',
            'Fallo en instalación de luminarias',
            'Equipo sin calibrar detectado',
            'Acceso no autorizado a zona de trabajo',
            'Falta de EPI en puesto de trabajo',
            'Interruptor diferencial disparado repetidamente',
            'Retraso en entrega de materiales',
            'Avería en taladro percutor',
            'Herramienta deteriorada detectada en obra',
        ];

        $tiposIncidencia    = TipoIncidencia::cases();
        $prioridades        = [
            PrioridadIncidencia::BAJA,
            PrioridadIncidencia::MEDIA,
            PrioridadIncidencia::MEDIA,
            PrioridadIncidencia::ALTA,
            PrioridadIncidencia::URGENTE,
        ];

        foreach ($trabajadores as $trabajador) {
            $cantidad = $faker->numberBetween(1, 4);
            for ($i = 0; $i < $cantidad; $i++) {
                $estado   = $faker->randomElement([
                    EstadoIncidencia::PENDIENTE,
                    EstadoIncidencia::PENDIENTE,
                    EstadoIncidencia::EN_PROCESO,
                    EstadoIncidencia::RESUELTA,
                    EstadoIncidencia::RESUELTA,
                    EstadoIncidencia::CERRADA,
                ]);
                $resuelta = in_array($estado, [EstadoIncidencia::RESUELTA, EstadoIncidencia::CERRADA]);

                Incidencia::create([
                    'trabajador_id' => $trabajador->id,
                    'tipo'          => $faker->randomElement($tiposIncidencia),
                    'prioridad'     => $faker->randomElement($prioridades),
                    'titulo'        => $faker->randomElement($titulos),
                    'descripcion'   => $faker->paragraph(3),
                    'estado'        => $estado,
                    'resolucion'    => $resuelta ? $faker->paragraph(2) : null,
                    'resuelto_por'  => $resuelta ? $aprobador->id : null,
                    'resuelto_at'   => $resuelta ? now()->subDays($faker->numberBetween(1, 60)) : null,
                ]);
            }
        }

        $ausencias   = Ausencia::count();
        $incidencias = Incidencia::count();
        $this->command?->info("  Listo: {$ausencias} ausencias, {$incidencias} incidencias.");
    }
}
