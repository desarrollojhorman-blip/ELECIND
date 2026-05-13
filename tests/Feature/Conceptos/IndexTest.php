<?php

namespace Tests\Feature\Conceptos;

use App\Livewire\Conceptos\Index;
use App\Models\Concepto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->administrador()->create();
        $admin->assignRole('administrador');

        return $admin;
    }

    private function trabajador(): User
    {
        $trabajador = User::factory()->trabajador()->create();
        $trabajador->assignRole('trabajador');

        return $trabajador;
    }

    public function test_un_admin_puede_ver_el_listado_de_conceptos(): void
    {
        $admin = $this->admin();
        Concepto::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('conceptos.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_por_no_tener_acceso_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('conceptos.index'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_un_concepto(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Cableado de baja tensión')
            ->set('form.descripcion', 'Tendido eléctrico para circuitos secundarios.')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('conceptos', [
            'nombre' => 'Cableado de baja tensión',
            'activo' => 1,
        ]);
    }

    public function test_validacion_nombre_obligatorio(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', '')
            ->call('guardar')
            ->assertHasErrors(['form.nombre' => 'required']);
    }

    public function test_validacion_nombre_unico(): void
    {
        $admin = $this->admin();
        Concepto::factory()->create(['nombre' => 'Tomas de corriente']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Tomas de corriente')
            ->call('guardar')
            ->assertHasErrors(['form.nombre' => 'unique']);
    }

    public function test_un_admin_puede_editar_un_concepto(): void
    {
        $admin = $this->admin();
        $concepto = Concepto::factory()->create(['nombre' => 'Original']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $concepto->id)
            ->assertSet('form.nombre', 'Original')
            ->set('form.nombre', 'Renombrado')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('conceptos', [
            'id' => $concepto->id,
            'nombre' => 'Renombrado',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_un_concepto(): void
    {
        $admin = $this->admin();
        $concepto = Concepto::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $concepto->id);

        $this->assertSoftDeleted('conceptos', ['id' => $concepto->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $concepto->id);

        $this->assertDatabaseHas('conceptos', [
            'id' => $concepto->id,
            'deleted_at' => null,
        ]);
    }

    public function test_buscador_filtra_por_nombre_y_descripcion(): void
    {
        $admin = $this->admin();
        Concepto::factory()->create(['nombre' => 'Iluminación interior', 'descripcion' => null]);
        Concepto::factory()->create(['nombre' => 'Otra cosa', 'descripcion' => 'Texto con iluminación']);
        Concepto::factory()->create(['nombre' => 'Sin relación', 'descripcion' => 'nada']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'iluminación')
            ->assertSee('Iluminación interior')
            ->assertSee('Otra cosa')
            ->assertDontSee('Sin relación');
    }

    public function test_filtro_inactivos_solo_muestra_los_desactivados(): void
    {
        $admin = $this->admin();
        Concepto::factory()->create(['nombre' => 'Activo X', 'activo' => true]);
        Concepto::factory()->create(['nombre' => 'Inactivo Y', 'activo' => false]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'inactivos')
            ->assertSee('Inactivo Y')
            ->assertDontSee('Activo X');
    }

    public function test_limpiar_filtros_resetea_estado_y_buscador_e_incrementa_reset_key(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'inactivos')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'activos')
            ->assertSet('resetKey', 1);
    }
}
