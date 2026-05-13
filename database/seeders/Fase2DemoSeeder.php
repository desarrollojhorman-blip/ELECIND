<?php

namespace Database\Seeders;

use App\Enums\EstadoAlbaran;
use App\Enums\TipoFirma;
use App\Enums\TipoHora;
use App\Models\Albaran;
use App\Models\AlbaranFirma;
use App\Models\AlbaranLineaMaterial;
use App\Models\AlbaranLineaPersonal;
use App\Models\AlbaranTokenFirma;
use App\Models\MaterialLote;
use App\Models\Proyecto;
use App\Services\NumeracionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class Fase2DemoSeeder extends Seeder
{
    public function run(NumeracionService $numeracion): void
    {
        if (! App::environment(['local', 'development', 'testing'])) {
            $this->command?->warn('Fase2DemoSeeder omitido: entorno no es local/development/testing.');

            return;
        }

        $proyectos = Proyecto::with(['cliente', 'usuarios'])->get();
        if ($proyectos->isEmpty()) {
            $this->command?->warn('Fase2DemoSeeder omitido: no hay proyectos. Ejecuta Fase1DemoSeeder primero.');

            return;
        }

        $this->command?->info('Sembrando datos de demo Fase 2 (albaranes)…');

        $lotesConStock = MaterialLote::query()
            ->where('stock_disponible', '>', 10)
            ->get();

        $estados = [
            EstadoAlbaran::BORRADOR,
            EstadoAlbaran::PENDIENTE_FIRMA,
            EstadoAlbaran::FIRMADO,
            EstadoAlbaran::FIRMADO,
            EstadoAlbaran::FACTURADO,
        ];

        foreach ($estados as $i => $estado) {
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
            $fecha = Carbon::now()->subDays(random_int(1, 60));

            /** @var Albaran $albaran */
            $albaran = Albaran::factory()->create([
                'numero' => $numeracion->siguienteNumeroAlbaran($fecha),
                'fecha' => $fecha,
                'cliente_id' => $proyecto->cliente_id,
                'proyecto_id' => $proyecto->id,
                'creado_por' => $creador->id,
                'responsable_id' => $responsable?->id,
                'estado' => $estado,
            ]);

            // Líneas de personal: 1-3
            $cantidadLineasPersonal = random_int(1, 3);
            for ($j = 0; $j < $cantidadLineasPersonal; $j++) {
                AlbaranLineaPersonal::factory()->create([
                    'albaran_id' => $albaran->id,
                    'trabajador_id' => $trabajadores->random()->id,
                    'tipo_hora' => fake()->randomElement(TipoHora::cases()),
                    'horas' => fake()->randomFloat(2, 1, 8),
                ]);
            }

            // Líneas de material: 0-3 (con stock real)
            if ($lotesConStock->isNotEmpty()) {
                $cantidadLineasMaterial = random_int(0, 3);
                for ($j = 0; $j < $cantidadLineasMaterial; $j++) {
                    /** @var MaterialLote $lote */
                    $lote = $lotesConStock->random();
                    $maxCantidad = (float) $lote->stock_disponible;
                    if ($maxCantidad <= 1) {
                        continue;
                    }

                    AlbaranLineaMaterial::factory()->create([
                        'albaran_id' => $albaran->id,
                        'material_lote_id' => $lote->id,
                        'cantidad' => fake()->randomFloat(2, 1, min($maxCantidad, 5)),
                    ]);

                    $lote->refresh();
                }
            }

            // Firmas según estado
            if (in_array($estado, [EstadoAlbaran::FIRMADO, EstadoAlbaran::FACTURADO], true)) {
                AlbaranFirma::factory()->create([
                    'albaran_id' => $albaran->id,
                    'tipo' => TipoFirma::TRABAJADOR,
                    'firmado_por_user_id' => $creador->id,
                    'firmado_at' => $fecha->copy()->addHours(2),
                ]);

                AlbaranFirma::factory()->create([
                    'albaran_id' => $albaran->id,
                    'tipo' => TipoFirma::RESPONSABLE,
                    'firmado_por_user_id' => $responsable?->id,
                    'firmado_at' => $fecha->copy()->addHours(3),
                ]);
            }

            // Token vigente para los pendientes_firma
            if ($estado === EstadoAlbaran::PENDIENTE_FIRMA && $responsable !== null) {
                AlbaranTokenFirma::factory()->create([
                    'albaran_id' => $albaran->id,
                    'tipo_firmante' => TipoFirma::RESPONSABLE,
                    'token' => Str::random(64),
                    'email_destino' => $responsable->email ?? 'responsable@demo.test',
                    'nombre_destino' => trim($responsable->nombre.' '.$responsable->apellidos),
                    'caduca_at' => Carbon::now()->addDays(7),
                ]);

                // Firma trabajador ya hecha
                AlbaranFirma::factory()->create([
                    'albaran_id' => $albaran->id,
                    'tipo' => TipoFirma::TRABAJADOR,
                    'firmado_por_user_id' => $creador->id,
                    'firmado_at' => $fecha->copy()->addHours(1),
                ]);
            }
        }

        $this->command?->info(sprintf(
            'Demo Fase 2 OK → %d albaranes (%d borrador · %d pendiente · %d firmado · %d facturado)',
            Albaran::count(),
            Albaran::where('estado', EstadoAlbaran::BORRADOR)->count(),
            Albaran::where('estado', EstadoAlbaran::PENDIENTE_FIRMA)->count(),
            Albaran::where('estado', EstadoAlbaran::FIRMADO)->count(),
            Albaran::where('estado', EstadoAlbaran::FACTURADO)->count(),
        ));
    }
}
