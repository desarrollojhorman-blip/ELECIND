<?php

namespace Tests\Feature\Clientes;

use App\Livewire\Clientes\Index;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
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
        Cliente::factory()->count(3)->create();

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
        // Rol custom con acceso web pero sin permisos → pasa middleware web pero no policy.
        $rolSinPermisos = Role::create([
            'name' => 'web_sin_permisos',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $sinPermiso = User::factory()->administrador()->create();
        $sinPermiso->assignRole($rolSinPermisos);

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

        $this->assertDatabaseHas('clientes', [
            'numero_cliente' => 1,
            'nombre' => 'Aluan Industrial SL',
            'cif' => 'B12345678',
        ]);
    }

    public function test_usuario_con_solo_permiso_de_ver_no_puede_abrir_crear(): void
    {
        $rolSoloVer = Role::create([
            'name' => 'clientes_solo_ver',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSoloVer->givePermissionTo('clientes.ver');

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSoloVer);

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirCrear')
            ->assertForbidden();
    }

    public function test_al_abrir_crear_sugiere_el_siguiente_numero_cliente(): void
    {
        $admin = $this->admin();
        Cliente::factory()->create(['numero_cliente' => 8]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->assertSet('form.numero_cliente', 9);
    }

    public function test_numero_cliente_debe_ser_unico(): void
    {
        $admin = $this->admin();
        Cliente::factory()->create(['numero_cliente' => 4]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.numero_cliente', 4)
            ->set('form.nombre', 'Cliente repetido')
            ->call('guardar')
            ->assertHasErrors(['form.numero_cliente' => 'unique']);
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
        $empresa = Cliente::factory()->create(['nombre' => 'Antiguo SL']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $empresa->id)
            ->assertSet('form.nombre', 'Antiguo SL')
            ->set('form.nombre', 'Renombrado SL')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'id' => $empresa->id,
            'nombre' => 'Renombrado SL',
        ]);
    }

    public function test_un_admin_puede_abrir_ver_en_modo_solo_lectura(): void
    {
        $admin = $this->admin();
        $cliente = Cliente::factory()->create(['nombre' => 'Cliente para ver']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $cliente->id)
            ->assertSet('modalAbierto', true)
            ->assertSet('modoSoloLectura', true)
            ->assertSet('form.nombre', 'Cliente para ver');
    }

    public function test_en_editar_se_muestran_los_proyectos_vinculados_del_cliente(): void
    {
        $admin = $this->admin();
        $cliente = Cliente::factory()->create();
        $otroCliente = Cliente::factory()->create();

        Proyecto::factory()->create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Proyecto Alpha',
        ]);
        Proyecto::factory()->create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Proyecto Beta',
        ]);
        Proyecto::factory()->create([
            'cliente_id' => $otroCliente->id,
            'nombre' => 'Proyecto Externo',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $cliente->id)
            ->assertSee('Proyecto Alpha')
            ->assertSee('Proyecto Beta')
            ->assertDontSee('Proyecto Externo');
    }

    public function test_en_editar_se_muestran_los_usuarios_vinculados_de_cada_proyecto(): void
    {
        $admin = $this->admin();
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Proyecto Usuarios',
        ]);
        $usuarioProyecto = User::factory()->administrador()->create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
        ]);

        $proyecto->usuarios()->attach($usuarioProyecto->id, ['rol_en_proyecto' => 'tecnico']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $cliente->id)
            ->assertSee('Ana Ruiz');
    }

    public function test_un_admin_puede_eliminar_y_restaurar_una_empresa(): void
    {
        $admin = $this->admin();
        $empresa = Cliente::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $empresa->id);

        $this->assertSoftDeleted('clientes', ['id' => $empresa->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $empresa->id);

        $this->assertDatabaseHas('clientes', [
            'id' => $empresa->id,
            'deleted_at' => null,
        ]);
    }

    public function test_usuario_sin_permiso_modificar_no_puede_editar_ni_restaurar(): void
    {
        $rolSoloVer = Role::create([
            'name' => 'clientes_solo_ver_update',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSoloVer->givePermissionTo('clientes.ver');

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSoloVer);

        $cliente = Cliente::factory()->create(['nombre' => 'No editable']);
        $cliente->delete();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirEditar', $cliente->id)
            ->assertForbidden();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('restaurar', $cliente->id)
            ->assertForbidden();
    }

    public function test_usuario_sin_permiso_eliminar_no_puede_confirmar_ni_eliminar(): void
    {
        $rolSinEliminar = Role::create([
            'name' => 'clientes_sin_eliminar',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSinEliminar->givePermissionTo(['clientes.ver', 'clientes.modificar']);

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSinEliminar);

        $cliente = Cliente::factory()->create(['nombre' => 'No eliminable']);

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('confirmarEliminar', $cliente->id)
            ->assertForbidden();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('eliminar', $cliente->id)
            ->assertForbidden();
    }

    public function test_el_buscador_filtra_por_nombre_cif_email_y_poblacion(): void
    {
        $admin = $this->admin();
        Cliente::factory()->create(['nombre' => 'Aluan Industrial SL']);
        Cliente::factory()->create(['nombre' => 'Otra empresa SL']);

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
            ->assertSet('filtroEstado', '')
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
            ->assertSet('filtroEstado', '')
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
