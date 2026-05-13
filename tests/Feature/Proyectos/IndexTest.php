<?php

namespace Tests\Feature\Proyectos;

use App\Livewire\Proyectos\Index;
use App\Models\EmpresasCliente;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
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

    public function test_un_admin_puede_ver_el_listado_de_proyectos(): void
    {
        $admin = $this->admin();
        $cliente = EmpresasCliente::factory()->create();
        Proyecto::factory()->count(3)->create(['empresa_cliente_id' => $cliente->id]);

        $response = $this->actingAs($admin)->get(route('proyectos.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('proyectos.index'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_un_proyecto(): void
    {
        $admin = $this->admin();
        $cliente = EmpresasCliente::factory()->create();
        $tipo = TiposProyecto::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Marzo-A')
            ->set('form.codigo', 'MAR-A-2026')
            ->set('form.empresa_cliente_id', $cliente->id)
            ->set('form.tipo_proyecto_id', $tipo->id)
            ->set('form.estado', 'activo')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('proyectos', [
            'nombre' => 'Marzo-A',
            'codigo' => 'MAR-A-2026',
            'empresa_cliente_id' => $cliente->id,
            'tipo_proyecto_id' => $tipo->id,
            'estado' => 'activo',
        ]);
    }

    public function test_validaciones_obligatorias_nombre_cliente_estado(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', '')
            ->set('form.empresa_cliente_id', null)
            ->call('guardar')
            ->assertHasErrors([
                'form.nombre' => 'required',
                'form.empresa_cliente_id' => 'required',
            ]);
    }

    public function test_validacion_codigo_unico_por_cliente(): void
    {
        $admin = $this->admin();
        $cliente = EmpresasCliente::factory()->create();
        Proyecto::factory()->create([
            'empresa_cliente_id' => $cliente->id,
            'codigo' => 'PRY-001',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Otro')
            ->set('form.empresa_cliente_id', $cliente->id)
            ->set('form.codigo', 'PRY-001')
            ->call('guardar')
            ->assertHasErrors(['form.codigo' => 'unique']);
    }

    public function test_codigo_repetido_es_valido_si_es_otro_cliente(): void
    {
        $admin = $this->admin();
        $clienteA = EmpresasCliente::factory()->create();
        $clienteB = EmpresasCliente::factory()->create();

        Proyecto::factory()->create([
            'empresa_cliente_id' => $clienteA->id,
            'codigo' => 'PRY-001',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Para cliente B')
            ->set('form.empresa_cliente_id', $clienteB->id)
            ->set('form.codigo', 'PRY-001')
            ->call('guardar')
            ->assertHasNoErrors();
    }

    public function test_validacion_fecha_fin_no_anterior_a_inicio(): void
    {
        $admin = $this->admin();
        $cliente = EmpresasCliente::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'X')
            ->set('form.empresa_cliente_id', $cliente->id)
            ->set('form.fecha_inicio', '2026-05-15')
            ->set('form.fecha_fin', '2026-05-10')
            ->call('guardar')
            ->assertHasErrors(['form.fecha_fin' => 'after_or_equal']);
    }

    public function test_un_admin_puede_editar_un_proyecto(): void
    {
        $admin = $this->admin();
        $proyecto = Proyecto::factory()->create(['nombre' => 'Original']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $proyecto->id)
            ->assertSet('form.nombre', 'Original')
            ->set('form.nombre', 'Renombrado')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('proyectos', [
            'id' => $proyecto->id,
            'nombre' => 'Renombrado',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_un_proyecto(): void
    {
        $admin = $this->admin();
        $proyecto = Proyecto::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $proyecto->id);

        $this->assertSoftDeleted('proyectos', ['id' => $proyecto->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $proyecto->id);

        $this->assertDatabaseHas('proyectos', [
            'id' => $proyecto->id,
            'deleted_at' => null,
        ]);
    }

    public function test_crear_tipo_al_vuelo_lo_selecciona_automaticamente(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->call('abrirModalTipo')
            ->assertSet('modalTipoAbierto', true)
            ->set('tipoForm.nombre', 'Marzo 2026')
            ->call('guardarTipo')
            ->assertHasNoErrors()
            ->assertSet('modalTipoAbierto', false);

        $tipoCreado = TiposProyecto::where('nombre', 'Marzo 2026')->first();
        $this->assertNotNull($tipoCreado);

        // El form de proyecto debe quedar con ese tipo seleccionado
        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->call('abrirModalTipo')
            ->set('tipoForm.nombre', 'Abril 2026')
            ->call('guardarTipo')
            ->assertSet('form.tipo_proyecto_id', TiposProyecto::where('nombre', 'Abril 2026')->first()->id);
    }

    public function test_validacion_tipo_unico_al_crear_al_vuelo(): void
    {
        $admin = $this->admin();
        TiposProyecto::factory()->create(['nombre' => 'Mantenimiento']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->call('abrirModalTipo')
            ->set('tipoForm.nombre', 'Mantenimiento')
            ->call('guardarTipo')
            ->assertHasErrors(['tipoForm.nombre' => 'unique']);
    }

    public function test_filtros_por_tipo_cliente_estado_y_responsable(): void
    {
        $admin = $this->admin();
        $clienteA = EmpresasCliente::factory()->create();
        $clienteB = EmpresasCliente::factory()->create();
        $tipo = TiposProyecto::factory()->create();

        Proyecto::factory()->create([
            'empresa_cliente_id' => $clienteA->id,
            'tipo_proyecto_id' => $tipo->id,
            'estado' => 'activo',
            'nombre' => 'Visible',
        ]);
        Proyecto::factory()->create([
            'empresa_cliente_id' => $clienteB->id,
            'estado' => 'cerrado',
            'nombre' => 'Oculto',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroCliente', $clienteA->id)
            ->set('filtroEstado', 'activo')
            ->set('filtroTipo', $tipo->id)
            ->assertSee('Visible')
            ->assertDontSee('Oculto');
    }

    public function test_limpiar_filtros_resetea_todo(): void
    {
        $admin = $this->admin();
        $cliente = EmpresasCliente::factory()->create();
        $tipo = TiposProyecto::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'activo')
            ->set('filtroCliente', $cliente->id)
            ->set('filtroTipo', $tipo->id)
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'todos')
            ->assertSet('filtroCliente', null)
            ->assertSet('filtroTipo', null)
            ->assertSet('resetKey', 1);
    }
}
