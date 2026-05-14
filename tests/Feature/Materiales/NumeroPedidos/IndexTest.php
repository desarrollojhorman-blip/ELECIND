<?php

namespace Tests\Feature\Materiales\NumeroPedidos;

use App\Livewire\Materiales\NumeroPedidos\Index;
use App\Models\NumeroPedido;
use App\Models\Role;
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

    public function test_un_admin_puede_ver_el_listado_de_pedidos(): void
    {
        $admin = $this->admin();
        NumeroPedido::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('materiales.pedidos'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('materiales.pedidos'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_un_pedido(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.numero', 'PED-TEST-001')
            ->set('form.fecha', '2026-05-14')
            ->set('form.proveedor', 'Proveedor test')
            ->set('form.descripcion', 'Pedido de prueba')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('numero_pedidos', [
            'numero' => 'PED-TEST-001',
            'proveedor' => 'Proveedor test',
        ]);
    }

    public function test_un_admin_puede_editar_un_pedido(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create(['numero' => 'PED-ORIGINAL']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $pedido->id)
            ->assertSet('form.numero', 'PED-ORIGINAL')
            ->set('form.numero', 'PED-MODIFICADO')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('numero_pedidos', [
            'id' => $pedido->id,
            'numero' => 'PED-MODIFICADO',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_un_pedido(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $pedido->id);

        $this->assertSoftDeleted('numero_pedidos', ['id' => $pedido->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('restaurar', $pedido->id);

        $this->assertDatabaseHas('numero_pedidos', ['id' => $pedido->id, 'deleted_at' => null]);
    }

    public function test_un_admin_puede_abrir_ver_en_modo_solo_lectura(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create(['numero' => 'PED-LECTURA']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $pedido->id)
            ->assertSet('modoSoloLectura', true)
            ->assertSet('modalAbierto', true)
            ->assertSet('form.numero', 'PED-LECTURA');
    }

    public function test_guardar_en_modo_solo_lectura_devuelve_403(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $pedido->id)
            ->assertSet('modoSoloLectura', true)
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_usuario_con_solo_permiso_ver_no_puede_editar(): void
    {
        $rolSoloVer = Role::create([
            'name' => 'pedidos_solo_ver',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSoloVer->givePermissionTo('pedidos.ver');

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSoloVer);

        $pedido = NumeroPedido::factory()->create();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirEditar', $pedido->id)
            ->assertForbidden();
    }

    public function test_no_se_pueden_anadir_materiales_inline_sin_permiso_de_crear_materiales(): void
    {
        $rolPedidosSinMateriales = Role::create([
            'name' => 'pedidos_sin_materiales_crear',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolPedidosSinMateriales->givePermissionTo([
            'pedidos.ver',
            'pedidos.modificar',
        ]);

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolPedidosSinMateriales);

        $pedido = NumeroPedido::factory()->create();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirEditar', $pedido->id)
            ->set('matDescripcion', 'Material sin permiso')
            ->set('matUnidad', 'ud')
            ->set('matStock', 3)
            ->call('agregarMaterialPendiente')
            ->assertForbidden();
    }
}