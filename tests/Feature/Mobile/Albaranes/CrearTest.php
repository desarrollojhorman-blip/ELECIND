<?php

namespace Tests\Feature\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Livewire\Mobile\Albaranes\Crear;
use App\Models\Albaran;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function trabajador(): User
    {
        $user = User::factory()->trabajador()->create();
        $user->assignRole('trabajador');

        return $user;
    }

    private function proyectoCompleto(User $trabajador): Proyecto
    {
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create(['cliente_id' => $cliente->id]);
        $proyecto->usuarios()->attach($trabajador->getKey(), ['rol_en_proyecto' => 'trabajador']);

        return $proyecto;
    }

    public function test_un_trabajador_puede_acceder_al_form_de_crear_parte(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('mobile.albaranes.nuevo'));

        $response->assertOk();
        $response->assertSeeLivewire(Crear::class);
    }

    public function test_un_trabajador_puede_crear_un_albaran_con_sus_propias_horas(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->set('form.mi_horas', '8.00')
            ->set('form.mi_horas_extra', '1.50')
            ->set('form.tipo_dia', 'laborable')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('albaranes', [
            'creado_por' => $trabajador->getKey(),
            'proyecto_id' => $proyecto->id,
            'cliente_id' => $proyecto->cliente_id,
            'estado' => EstadoAlbaran::BORRADOR->value,
        ]);

        $albaran = Albaran::query()->where('creado_por', $trabajador->getKey())->firstOrFail();
        $this->assertCount(1, $albaran->lineasPersonal);
        $this->assertSame($trabajador->getKey(), $albaran->lineasPersonal->first()?->trabajador_id);
    }

    public function test_al_seleccionar_proyecto_se_autosincroniza_el_cliente(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->assertSet('form.cliente_id', $proyecto->cliente_id);
    }

    public function test_validacion_proyecto_obligatorio(): void
    {
        $trabajador = $this->trabajador();

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', null)
            ->call('guardar')
            ->assertHasErrors(['form.proyecto_id' => 'required']);
    }

    public function test_validacion_mi_horas_no_puede_ser_negativo(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->set('form.mi_horas', '-1')
            ->call('guardar')
            ->assertHasErrors(['form.mi_horas' => 'min']);
    }

    public function test_validacion_tipo_dia_obligatorio(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->set('form.tipo_dia', 'inventado')
            ->call('guardar')
            ->assertHasErrors(['form.tipo_dia']);
    }

    public function test_horas_extra_se_guardan_correctamente(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->set('form.tipo_dia', 'festivo')
            ->set('form.mi_horas', '6.00')
            ->set('form.mi_horas_extra', '2.50')
            ->call('guardar')
            ->assertHasNoErrors();

        $albaran = Albaran::query()->where('creado_por', $trabajador->getKey())->firstOrFail();
        $this->assertSame('festivo', $albaran->tipo_dia->value);
        $miLinea = $albaran->lineasPersonal->first();
        $this->assertSame('6.00', (string) $miLinea?->horas);
        $this->assertSame('2.50', (string) $miLinea?->horas_extra);
    }

    public function test_se_puede_anadir_un_companero_y_se_guarda_como_linea_personal(): void
    {
        $trabajador = $this->trabajador();
        $companero = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);
        $proyecto->usuarios()->attach($companero->getKey(), ['rol_en_proyecto' => 'trabajador']);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->call('addCompanero')
            ->set('form.companeros.0.trabajador_id', $companero->getKey())
            ->set('form.companeros.0.horas', '8.00')
            ->set('form.companeros.0.horas_extra', '2.50')
            ->call('guardar')
            ->assertHasNoErrors();

        $albaran = Albaran::query()->where('creado_por', $trabajador->getKey())->firstOrFail();
        $this->assertCount(2, $albaran->lineasPersonal);
        $this->assertTrue($albaran->lineasPersonal->contains('trabajador_id', $companero->getKey()));
    }

    public function test_no_se_puede_anadir_a_si_mismo_como_companero(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->call('addCompanero')
            ->set('form.companeros.0.trabajador_id', $trabajador->getKey())
            ->call('guardar')
            ->assertHasErrors(['form.companeros.0.trabajador_id']);
    }

    public function test_al_anadir_material_se_descuenta_stock_del_material_via_observer(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);
        $material = Material::factory()->create(['stock' => 100]);
        $proyecto->materiales()->attach($material->id);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->call('addMaterial')
            ->set('form.materiales.0.material_id', $material->id)
            ->set('form.materiales.0.cantidad', '15')
            ->call('guardar')
            ->assertHasNoErrors();

        $materialActualizado = Material::findOrFail($material->id);
        $this->assertSame('85.00', (string) $materialActualizado->stock);
    }

    public function test_se_permite_stock_negativo_en_el_material(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);
        $material = Material::factory()->create(['stock' => 10]);
        $proyecto->materiales()->attach($material->id);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->call('addMaterial')
            ->set('form.materiales.0.material_id', $material->id)
            ->set('form.materiales.0.cantidad', '25')
            ->call('guardar')
            ->assertHasNoErrors();

        $materialActualizado = Material::findOrFail($material->id);
        $this->assertSame('-15.00', (string) $materialActualizado->stock);
    }

    public function test_se_genera_un_numero_unico_para_el_albaran(): void
    {
        $trabajador = $this->trabajador();
        $proyecto = $this->proyectoCompleto($trabajador);

        Livewire::actingAs($trabajador)
            ->test(Crear::class)
            ->set('form.proyecto_id', $proyecto->id)
            ->call('guardar');

        $albaran = Albaran::query()->where('creado_por', $trabajador->getKey())->firstOrFail();
        $this->assertNotEmpty($albaran->numero);
        $this->assertStringContainsString((string) now()->year, $albaran->numero);
    }
}
