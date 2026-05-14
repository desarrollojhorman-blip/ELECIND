<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Concepto;
use App\Models\FamiliaMaterial;
use App\Models\Material;
use App\Models\NumeroPedido;
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

        // Trabajador demo estable (creado en AdminUsersSeeder con username "trabajador").
        // Garantizamos que esté en TODOS los proyectos demo para que se pueda usar como
        // login de prueba siempre: `trabajador / password` y ver todo el contenido.
        $trabajadorDemo = User::query()->where('username', 'trabajador')->first();

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

        // ─── Familias de material ─────────────────────────────────────────
        // 4 familias demo con nombres realistas.
        $familias = collect([
            ['nombre' => 'Cables H07V-K', 'descripcion' => 'Cable flexible 750V para instalaciones interiores'],
            ['nombre' => 'Mecanismos', 'descripcion' => 'Interruptores, enchufes y placas'],
            ['nombre' => 'Tubos corrugados', 'descripcion' => 'Canalización flexible para cableado'],
            ['nombre' => 'Cuadros eléctricos', 'descripcion' => 'Armarios y cajas de protección'],
        ])->map(fn (array $datos) => FamiliaMaterial::create($datos));

        // ─── Pedidos + materiales ─────────────────────────────────────────
        // 6 pedidos demo, cada uno con 4-7 materiales. ~70% de los materiales
        // recibe una familia aleatoria; el resto queda sin familia para que
        // el filtro "Sin familia" tenga datos en demo.
        $pedidos = NumeroPedido::factory()->count(6)->create();

        $materiales = collect();
        $pedidos->each(function (NumeroPedido $pedido) use (&$materiales, $familias): void {
            $nuevos = Material::factory()
                ->count(random_int(4, 7))
                ->create(['numero_pedido_id' => $pedido->id]);

            $nuevos->each(function (Material $material) use ($familias): void {
                if (random_int(1, 100) <= 70) {
                    $material->familia_id = $familias->random()->id;
                    $material->save();
                }
            });

            $materiales = $materiales->concat($nuevos);
        });

        // ─── Proyectos + asignaciones ─────────────────────────────────────
        $empresas->each(function (Cliente $empresa) use ($tipos, $conceptos, $materiales, $trabajadores, $responsables, $trabajadorDemo): void {
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

                // Trabajador demo siempre asignado a TODOS los proyectos.
                if ($trabajadorDemo !== null) {
                    $proyecto->usuarios()->syncWithoutDetaching([
                        $trabajadorDemo->id => ['rol_en_proyecto' => 'trabajador'],
                    ]);
                }

                if ($responsableEmpresa !== null) {
                    $proyecto->usuarios()->syncWithoutDetaching([
                        $responsableEmpresa->id => ['rol_en_proyecto' => 'responsable'],
                    ]);
                }

                $idsConceptos = $conceptos->random(random_int(3, 6))->pluck('id')->all();
                $proyecto->conceptos()->syncWithoutDetaching($idsConceptos);

                // Asignar 5-10 materiales aleatorios al proyecto (pivot material_proyecto).
                $cantidadMateriales = min(random_int(5, 10), $materiales->count());
                $idsMateriales = $materiales->random($cantidadMateriales)->pluck('id')->all();
                $proyecto->materiales()->syncWithoutDetaching($idsMateriales);
            }
        });

        $this->command?->info(sprintf(
            'Demo OK → %d trabajadores · %d tipos · %d conceptos · %d empresas · %d responsables · %d familias · %d pedidos · %d materiales · %d proyectos',
            $trabajadores->count(),
            $tipos->count(),
            $conceptos->count(),
            $empresas->count(),
            $responsables->count(),
            $familias->count(),
            $pedidos->count(),
            $materiales->count(),
            Proyecto::count(),
        ));
    }
}
