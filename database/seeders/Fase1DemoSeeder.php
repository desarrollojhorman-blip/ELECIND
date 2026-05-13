<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\Material;
use App\Models\MaterialLote;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class Fase1DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! App::environment(['local', 'development', 'testing'])) {
            $this->command?->warn('Fase1DemoSeeder omitido: entorno no es local/development/testing.');

            return;
        }

        $this->command?->info('Sembrando datos de demo Fase 1…');

        $trabajadores = User::factory()
            ->count(8)
            ->trabajador()
            ->create()
            ->each(fn (User $u) => $u->assignRole('trabajador'));

        $tipos = TiposProyecto::factory()->count(6)->create();

        $conceptos = Concepto::factory()->count(15)->create();

        $empresas = Cliente::factory()->count(5)->create();

        $responsables = $empresas->map(function (Cliente $empresa): User {
            $responsable = User::factory()
                ->responsableDe($empresa->id)
                ->create();
            $responsable->assignRole('responsable');

            return $responsable;
        });

        $materiales = Material::factory()->count(30)->create();

        $materiales->each(function (Material $material): void {
            MaterialLote::factory()
                ->count(random_int(1, 2))
                ->create(['material_id' => $material->id]);
        });

        $empresas->each(function (Cliente $empresa) use ($tipos, $conceptos, $materiales, $trabajadores, $responsables): void {
            $responsableEmpresa = $responsables->firstWhere('cliente_id', $empresa->id);
            $cantidadProyectos = random_int(2, 3);

            for ($i = 0; $i < $cantidadProyectos; $i++) {
                /** @var Proyecto $proyecto */
                $proyecto = Proyecto::factory()->create([
                    'cliente_id' => $empresa->id,
                    'tipo_proyecto_id' => $tipos->random()->id,
                    'responsable_principal_id' => $responsableEmpresa?->id,
                ]);

                $idsTrabajadores = $trabajadores->random(random_int(1, 3))->pluck('id')->all();
                $proyecto->usuarios()->syncWithoutDetaching(
                    array_fill_keys($idsTrabajadores, ['rol_en_proyecto' => 'trabajador'])
                );

                if ($responsableEmpresa !== null) {
                    $proyecto->usuarios()->syncWithoutDetaching([
                        $responsableEmpresa->id => ['rol_en_proyecto' => 'responsable'],
                    ]);
                }

                $idsConceptos = $conceptos->random(random_int(3, 6))->pluck('id')->all();
                $proyecto->conceptos()->syncWithoutDetaching($idsConceptos);

                $pivotMateriales = $materiales
                    ->random(random_int(5, 10))
                    ->mapWithKeys(fn (Material $m): array => [
                        $m->id => ['cantidad_prevista' => random_int(5, 100)],
                    ])
                    ->all();
                $proyecto->materiales()->syncWithoutDetaching($pivotMateriales);
            }
        });

        $this->command?->info(sprintf(
            'Demo OK → %d trabajadores · %d tipos · %d conceptos · %d empresas · %d responsables · %d materiales · %d proyectos',
            $trabajadores->count(),
            $tipos->count(),
            $conceptos->count(),
            $empresas->count(),
            $responsables->count(),
            $materiales->count(),
            Proyecto::count(),
        ));
    }
}
