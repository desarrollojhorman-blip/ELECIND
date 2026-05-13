<?php

namespace Tests\Feature\Clientes;

use App\Livewire\Clientes\Index;
use App\Models\EmpresasCliente;
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

    public function test_un_admin_puede_ver_el_listado_de_empresas(): void
    {
        $admin = $this->admin();
        EmpresasCliente::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('clientes.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login_por_no_tener_acceso_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('clientes.index'));

        $response->assertRedirect('/login');
    }

    public function test_un_usuario_web_sin_permiso_clientes_ver_recibe_403(): void
    {
        $sinPermiso = User::factory()->administrador()->create();
        // Asignamos rol "trabajador" pero con acceso web manualmente para llegar a la policy.
        $sinPermiso->update(['acceso' => 'web']);
        $sinPermiso->assignRole('trabajador');

        $response = $this->actingAs($sinPermiso)->get(route('clientes.index'));

        $response->assertForbidden();
    }

    public function test_un_admin_puede_crear_una_empresa(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Aluan Industrial SL')
            ->set('form.cif', 'B12345678')
            ->set('form.email', 'contacto@aluan.test')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('empresas_clientes', [
            'nombre' => 'Aluan Industrial SL',
            'cif' => 'B12345678',
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

    public function test_un_admin_puede_editar_una_empresa(): void
    {
        $admin = $this->admin();
        $empresa = EmpresasCliente::factory()->create(['nombre' => 'Antiguo SL']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $empresa->id)
            ->assertSet('form.nombre', 'Antiguo SL')
            ->set('form.nombre', 'Renombrado SL')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('empresas_clientes', [
            'id' => $empresa->id,
            'nombre' => 'Renombrado SL',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_una_empresa(): void
    {
        $admin = $this->admin();
        $empresa = EmpresasCliente::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $empresa->id);

        $this->assertSoftDeleted('empresas_clientes', ['id' => $empresa->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $empresa->id);

        $this->assertDatabaseHas('empresas_clientes', [
            'id' => $empresa->id,
            'deleted_at' => null,
        ]);
    }

    public function test_el_buscador_filtra_por_nombre_cif_email_y_poblacion(): void
    {
        $admin = $this->admin();
        EmpresasCliente::factory()->create(['nombre' => 'Aluan Industrial SL']);
        EmpresasCliente::factory()->create(['nombre' => 'Otra empresa SL']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'Aluan')
            ->assertSee('Aluan Industrial SL')
            ->assertDontSee('Otra empresa SL');
    }

    public function test_limpiar_filtros_resetea_buscador_estado_y_provincia_e_incrementa_reset_key(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'activas')
            ->set('filtroProvincia', 'Madrid')
            ->assertSet('buscar', 'algo')
            ->assertSet('filtroEstado', 'activas')
            ->assertSet('filtroProvincia', 'Madrid')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'todos')
            ->assertSet('filtroProvincia', '')
            ->assertSet('resetKey', 1);
    }

    public function test_limpiar_buscador_solo_borra_el_buscador(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'activas')
            ->call('limpiarBuscador')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'activas');
    }

    public function test_quitar_filtro_individual_incrementa_reset_key(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'activas')
            ->set('filtroProvincia', 'Madrid')
            ->call('quitarFiltroEstado')
            ->assertSet('filtroEstado', 'todos')
            ->assertSet('filtroProvincia', 'Madrid')
            ->assertSet('resetKey', 1)
            ->call('quitarFiltroProvincia')
            ->assertSet('filtroProvincia', '')
            ->assertSet('resetKey', 2);
    }

    public function test_tiene_algo_que_limpiar_es_false_inicialmente_y_true_si_hay_busqueda_o_filtros(): void
    {
        $admin = $this->admin();

        $component = Livewire::actingAs($admin)->test(Index::class);

        $this->assertFalse($component->instance()->tieneAlgoQueLimpiar);

        $component->set('buscar', 'x');
        $this->assertTrue($component->instance()->tieneAlgoQueLimpiar);

        $component->call('limpiarFiltros');
        $this->assertFalse($component->instance()->tieneAlgoQueLimpiar);

        $component->set('filtroEstado', 'activas');
        $this->assertTrue($component->instance()->tieneAlgoQueLimpiar);
    }
}
