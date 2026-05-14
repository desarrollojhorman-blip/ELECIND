<?php

namespace Tests\Feature\Materiales;

use App\Livewire\Materiales\Index;
use App\Models\FamiliaMaterial;
use App\Models\Material;
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
        $t = User::factory()->trabajador()->create();
        $t->assignRole('trabajador');

        return $t;
    }

    public function test_un_admin_puede_ver_el_listado_de_materiales(): void
    {
        $admin = $this->admin();
        Material::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('materiales.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('materiales.index'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_un_material(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.numero_pedido_id', $pedido->id)
            ->set('form.descripcion', 'Cable test')
            ->set('form.unidad_medida', 'm')
            ->set('form.stock', 50)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('materiales', [
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Cable test',
            'unidad_medida' => 'm',
        ]);
    }

    public function test_validaciones_obligatorias(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.numero_pedido_id', null)
            ->set('form.descripcion', '')
            ->set('form.unidad_medida', '')
            ->call('guardar')
            ->assertHasErrors([
                'form.numero_pedido_id' => 'required',
                'form.descripcion' => 'required',
                'form.unidad_medida' => 'required',
            ]);
    }

    public function test_un_admin_puede_editar_un_material(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create(['descripcion' => 'Original']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $material->id)
            ->assertSet('form.descripcion', 'Original')
            ->set('form.descripcion', 'Modificado')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'descripcion' => 'Modificado',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_un_material(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $material->id);

        $this->assertSoftDeleted('materiales', ['id' => $material->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('restaurar', $material->id);

        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'deleted_at' => null]);
    }

    public function test_filtro_por_pedido(): void
    {
        $admin = $this->admin();
        $pedidoA = NumeroPedido::factory()->create(['numero' => 'PED-A-001']);
        $pedidoB = NumeroPedido::factory()->create(['numero' => 'PED-B-001']);
        Material::factory()->create(['numero_pedido_id' => $pedidoA->id, 'descripcion' => 'Cable visible']);
        Material::factory()->create(['numero_pedido_id' => $pedidoB->id, 'descripcion' => 'Bombilla oculta']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroPedido', (string) $pedidoA->id)
            ->assertSee('Cable visible')
            ->assertDontSee('Bombilla oculta');
    }

    public function test_limpiar_filtros_resetea_buscador_y_pedido(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroPedido', '5')
            ->set('filtroFamilia', '3')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroPedido', '')
            ->assertSet('filtroFamilia', '')
            ->assertSet('resetKey', 1);
    }

    public function test_filtro_por_familia(): void
    {
        $admin = $this->admin();
        $famCables = FamiliaMaterial::factory()->create(['nombre' => 'Cables H07V-K']);
        $famTubos = FamiliaMaterial::factory()->create(['nombre' => 'Tubos corrugados']);
        Material::factory()->create(['familia_id' => $famCables->id, 'descripcion' => 'Cable rojo visible']);
        Material::factory()->create(['familia_id' => $famTubos->id, 'descripcion' => 'Tubo gris oculto']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroFamilia', (string) $famCables->id)
            ->assertSee('Cable rojo visible')
            ->assertDontSee('Tubo gris oculto');
    }

    public function test_filtro_sin_familia_muestra_solo_materiales_huerfanos(): void
    {
        $admin = $this->admin();
        $fam = FamiliaMaterial::factory()->create();
        Material::factory()->create(['familia_id' => null, 'descripcion' => 'Material huerfano']);
        Material::factory()->create(['familia_id' => $fam->id, 'descripcion' => 'Material con familia']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroFamilia', 'sin_familia')
            ->assertSee('Material huerfano')
            ->assertDontSee('Material con familia');
    }

    public function test_un_admin_puede_asignar_familia_al_crear_un_material(): void
    {
        $admin = $this->admin();
        $pedido = NumeroPedido::factory()->create();
        $fam = FamiliaMaterial::factory()->create(['nombre' => 'Mecanismos']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.numero_pedido_id', $pedido->id)
            ->set('form.familia_id', $fam->id)
            ->set('form.descripcion', 'Interruptor simple')
            ->set('form.unidad_medida', 'ud')
            ->set('form.stock', 10)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('materiales', [
            'descripcion' => 'Interruptor simple',
            'familia_id' => $fam->id,
        ]);
    }

    public function test_un_admin_puede_abrir_ver_en_modo_solo_lectura(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create(['descripcion' => 'Cable test']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $material->id)
            ->assertSet('modoSoloLectura', true)
            ->assertSet('modalAbierto', true)
            ->assertSet('form.descripcion', 'Cable test');
    }

    public function test_cerrar_modal_desde_ver_resetea_modo_solo_lectura(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $material->id)
            ->assertSet('modoSoloLectura', true)
            ->call('cerrarModal')
            ->assertSet('modoSoloLectura', false)
            ->assertSet('modalAbierto', false);
    }

    public function test_guardar_en_modo_solo_lectura_devuelve_403(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $material->id)
            ->assertSet('modoSoloLectura', true)
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_usuario_con_solo_permiso_ver_no_puede_editar(): void
    {
        $rolSoloVer = Role::create([
            'name' => 'materiales_solo_ver',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSoloVer->givePermissionTo('materiales.ver');

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSoloVer);

        $material = Material::factory()->create();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirEditar', $material->id)
            ->assertForbidden();
    }
}
