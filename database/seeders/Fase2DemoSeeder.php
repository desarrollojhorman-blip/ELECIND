<?php

namespace Database\Seeders;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoFirma;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\Firma;
use App\Models\TokenFirma;
use App\Models\Material;
use App\Models\Proyecto;
use App\Services\NumeracionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class Fase2DemoSeeder extends Seeder
{
    // Weighted tipo_hora: 60% laboral, 20% laboral_noche, 14% festivo, 6% festivo_noche
    private const TIPO_HORA_POOL = [
        TipoHora::LABORAL, TipoHora::LABORAL, TipoHora::LABORAL, TipoHora::LABORAL,
        TipoHora::LABORAL, TipoHora::LABORAL,
        TipoHora::LABORAL_NOCHE, TipoHora::LABORAL_NOCHE,
        TipoHora::FESTIVO, TipoHora::FESTIVO,
        TipoHora::FESTIVO_NOCHE,
    ];

    public function run(NumeracionService $numeracion): void
    {
        if (! App::environment(['local', 'development', 'testing'])) {
            $this->command?->warn('Fase2DemoSeeder omitido: entorno no es local/development/testing.');

            return;
        }

        // Solo proyectos activos: un albarán sobre un proyecto cerrado/inactivo
        // dejaría el selector del editor vacío y daría datos demo incongruentes.
        $proyectos = Proyecto::with(['cliente', 'usuarios'])
            ->where('estado', 'activo')
            ->get();
        if ($proyectos->isEmpty()) {
            $this->command?->warn('Fase2DemoSeeder omitido: no hay proyectos activos. Ejecuta Fase1DemoSeeder primero.');

            return;
        }

        $this->command?->info('Sembrando datos de demo Fase 2 (albaranes)…');

        $materialesConStock = Material::query()->where('stock', '>', 5)->get();

        $total = 70;
        $creados = 0;

        for ($i = 0; $i < $total; $i++) {
            /** @var Proyecto $proyecto */
            $proyecto = $proyectos->random();

            $trabajadores = $proyecto->usuarios()
                ->wherePivot('rol_en_proyecto', 'trabajador')
                ->get();
            $responsable = $proyecto->usuarios()
                ->wherePivot('rol_en_proyecto', 'responsable')
                ->first();

            if ($trabajadores->isEmpty()) {
                continue;
            }

            $creador = $trabajadores->random();
            // Spread across last 6 months
            $diasAtras = random_int(0, 180);
            $fecha = Carbon::now()->subDays($diasAtras);

            $estado = $this->elegirEstado($diasAtras);
            $tipoHora = self::TIPO_HORA_POOL[array_rand(self::TIPO_HORA_POOL)];

            /** @var Albaran $albaran */
            $albaran = Albaran::factory()->create([
                'numero'         => $numeracion->siguienteNumeroAlbaran($fecha),
                'fecha'          => $fecha->format('Y-m-d'),
                'cliente_id'     => $proyecto->cliente_id,
                'proyecto_id'    => $proyecto->id,
                'creado_por'     => $creador->id,
                'responsable_id' => $responsable?->id,
                'estado'         => $estado,
                'tipo_hora'      => $tipoHora,
            ]);

            // 1-4 líneas de personal por albarán
            $numLineas = random_int(1, 4);
            for ($j = 0; $j < $numLineas; $j++) {
                $trabajador = $trabajadores->random();
                $horas = $this->horasReales();
                $horasExtra = random_int(1, 100) <= 25
                    ? round(random_int(5, 30) / 10, 1)  // 0.5h – 3.0h
                    : 0;

                AlbaranLineaPersonal::factory()->create([
                    'albaran_id'   => $albaran->id,
                    'trabajador_id' => $trabajador->id,
                    'horas'        => $horas,
                    'horas_extra'  => $horasExtra,
                ]);
            }

            // 0-3 líneas de material
            if ($materialesConStock->isNotEmpty() && random_int(1, 100) <= 55) {
                $numMateriales = random_int(1, 3);
                for ($j = 0; $j < $numMateriales; $j++) {
                    /** @var Material $material */
                    $material = $materialesConStock->random();
                    $maxCantidad = (int) $material->stock;
                    if ($maxCantidad < 1) {
                        continue;
                    }

                    AlbaranLineaMaterial::factory()->create([
                        'albaran_id'  => $albaran->id,
                        'material_id' => $material->id,
                        'cantidad'    => random_int(1, min($maxCantidad, 10)),
                    ]);

                    $material->refresh();
                }
            }

            // Firmas según estado
            if (in_array($estado, [EstadoAlbaran::FIRMADO, EstadoAlbaran::FACTURADO], true)) {
                Firma::create([
                    'firmable_type'       => Albaran::class,
                    'firmable_id'         => $albaran->id,
                    'tipo'                => TipoFirma::TRABAJADOR,
                    'firmado_por_user_id' => $creador->id,
                    'firma_path'          => 'firmas/demo-trabajador.png',
                    'firmado_at'          => $fecha->copy()->addHours(2),
                ]);

                Firma::create([
                    'firmable_type'       => Albaran::class,
                    'firmable_id'         => $albaran->id,
                    'tipo'                => TipoFirma::RESPONSABLE,
                    'firmado_por_user_id' => $responsable?->id,
                    'firma_path'          => 'firmas/demo-responsable.png',
                    'firmado_at'          => $fecha->copy()->addHours(3),
                ]);
            }

            if ($estado === EstadoAlbaran::PENDIENTE_FIRMA && $responsable !== null) {
                TokenFirma::create([
                    'firmable_type'  => Albaran::class,
                    'firmable_id'    => $albaran->id,
                    'tipo_firmante'  => TipoFirma::RESPONSABLE,
                    'token'          => Str::random(64),
                    'email_destino'  => $responsable->email ?? 'responsable@demo.test',
                    'nombre_destino' => trim($responsable->nombre.' '.$responsable->apellidos),
                    'caduca_at'      => Carbon::now()->addDays(7),
                ]);

                Firma::create([
                    'firmable_type'       => Albaran::class,
                    'firmable_id'         => $albaran->id,
                    'tipo'                => TipoFirma::TRABAJADOR,
                    'firmado_por_user_id' => $creador->id,
                    'firma_path'          => 'firmas/demo-trabajador.png',
                    'firmado_at'          => $fecha->copy()->addHours(1),
                ]);
            }

            $creados++;
        }

        $this->command?->info(sprintf(
            'Demo Fase 2 OK → %d albaranes creados (%d pendiente · %d firmado · %d facturado)',
            $creados,
            Albaran::where('estado', EstadoAlbaran::PENDIENTE_FIRMA)->count(),
            Albaran::where('estado', EstadoAlbaran::FIRMADO)->count(),
            Albaran::where('estado', EstadoAlbaran::FACTURADO)->count(),
        ));
    }

    // Los albaranes recientes suelen estar pendientes de firma; los más antiguos,
    // firmados o ya facturados.
    private function elegirEstado(int $diasAtras): EstadoAlbaran
    {
        if ($diasAtras <= 14) {
            return fake()->randomElement([
                EstadoAlbaran::PENDIENTE_FIRMA, EstadoAlbaran::PENDIENTE_FIRMA,
                EstadoAlbaran::FIRMADO,
            ]);
        }

        if ($diasAtras <= 60) {
            return fake()->randomElement([
                EstadoAlbaran::PENDIENTE_FIRMA,
                EstadoAlbaran::FIRMADO, EstadoAlbaran::FIRMADO, EstadoAlbaran::FIRMADO,
                EstadoAlbaran::FACTURADO,
            ]);
        }

        return fake()->randomElement([
            EstadoAlbaran::FIRMADO, EstadoAlbaran::FIRMADO,
            EstadoAlbaran::FACTURADO, EstadoAlbaran::FACTURADO, EstadoAlbaran::FACTURADO,
        ]);
    }

    // Valores realistas: jornada completa (8h), media jornada (4h), parciales
    private function horasReales(): float
    {
        return fake()->randomElement([4.0, 4.0, 6.0, 7.0, 8.0, 8.0, 8.0, 8.5, 9.0, 10.0]);
    }
}
