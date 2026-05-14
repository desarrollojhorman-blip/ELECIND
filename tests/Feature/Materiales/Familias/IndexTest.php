<?php

namespace Tests\Feature\Materiales\Familias;

use App\Livewire\Materiales\Familias\Index;
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

    public function test_un_admin_puede_ver_el_listado_de_familias(): void
    {
        $admin = $this->admin();
        FamiliaMaterial::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('materiales.familias'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('materiales.familias'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_una_familia(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Cables H07V-K')
            ->set('form.descripcion', 'Cable flexible 750V')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('familias_material', [
            'nombre' => 'Cables H07V-K',
            'descripcion' => 'Cable flexible 750V',
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
        FamiliaMaterial::factory()->create(['nombre' => 'Mecanismos']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Mecanismos')
            ->call('guardar')
            ->assertHasErrors(['form.nombre' => 'unique']);
    }

    public function test_un_admin_puede_editar_una_familia(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create(['nombre' => 'Original']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->assertSet('form.nombre', 'Original')
            ->set('form.nombre', 'Modificada')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('familias_material', [
            'id' => $familia->id,
            'nombre' => 'Modificada',
        ]);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_una_familia(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $familia->id);

        $this->assertSoftDeleted('familias_material', ['id' => $familia->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('restaurar', $familia->id);

        $this->assertDatabaseHas('familias_material', ['id' => $familia->id, 'deleted_at' => null]);
    }

    public function test_eliminar_familia_deja_materiales_sin_familia(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $material = Material::factory()->create(['familia_id' => $familia->id]);

        $familia->forceDelete();

        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'familia_id' => null,
        ]);
    }

    public function test_un_admin_puede_abrir_ver_en_modo_solo_lectura(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create(['nombre' => 'Cables']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $familia->id)
            ->assertSet('modoSoloLectura', true)
            ->assertSet('modalAbierto', true)
            ->assertSet('form.nombre', 'Cables');
    }

    public function test_panel_materiales_muestra_los_asignados_a_la_familia(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $pedido = NumeroPedido::factory()->create();
        Material::factory()->create([
            'familia_id' => $familia->id,
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Material asignado',
        ]);
        Material::factory()->create([
            'familia_id' => null,
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Material huerfano',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $familia->id)
            ->assertSee('Material asignado')
            ->assertDontSee('Material huerfano');
    }

    public function test_modal_asignar_muestra_solo_huerfanos_por_defecto(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $otraFamilia = FamiliaMaterial::factory()->create();
        $pedido = NumeroPedido::factory()->create();
        Material::factory()->create([
            'familia_id' => null,
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Huerfano disponible',
        ]);
        Material::factory()->create([
            'familia_id' => $otraFamilia->id,
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Ocupado por otra familia',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->call('abrirModalAsignar')
            ->assertSet('mostrarTodosAsignar', false)
            ->assertSee('Huerfano disponible')
            ->assertDontSee('Ocupado por otra familia');
    }

    public function test_toggle_mostrar_todos_incluye_materiales_con_otra_familia(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $otraFamilia = FamiliaMaterial::factory()->create(['nombre' => 'Otra familia']);
        $pedido = NumeroPedido::factory()->create();
        Material::factory()->create([
            'familia_id' => $otraFamilia->id,
            'numero_pedido_id' => $pedido->id,
            'descripcion' => 'Reasignable',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->call('abrirModalAsignar')
            ->set('mostrarTodosAsignar', true)
            ->assertSee('Reasignable');
    }

    public function test_asignar_seleccionados_setea_familia_id_en_los_materiales(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create(['nombre' => 'Cables']);
        $pedido = NumeroPedido::factory()->create();
        $mat1 = Material::factory()->create(['familia_id' => null, 'numero_pedido_id' => $pedido->id]);
        $mat2 = Material::factory()->create(['familia_id' => null, 'numero_pedido_id' => $pedido->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->call('abrirModalAsignar')
            ->set('materialesSeleccionados', [$mat1->id, $mat2->id])
            ->call('asignarSeleccionados')
            ->assertSet('modalAsignarAbierto', false);

        $this->assertDatabaseHas('materiales', ['id' => $mat1->id, 'familia_id' => $familia->id]);
        $this->assertDatabaseHas('materiales', ['id' => $mat2->id, 'familia_id' => $familia->id]);
    }

    public function test_quitar_material_de_la_familia_pone_familia_id_a_null(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $material = Material::factory()->create(['familia_id' => $familia->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->call('quitarMaterialDeFamilia', $material->id);

        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'familia_id' => null]);
    }

    public function test_quitar_material_de_otra_familia_no_lo_modifica(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();
        $otraFamilia = FamiliaMaterial::factory()->create();
        $material = Material::factory()->create(['familia_id' => $otraFamilia->id]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->call('quitarMaterialDeFamilia', $material->id);

        // El material seguía perteneciendo a la "otraFamilia", no a la actual,
        // así que la operación no debe afectarlo.
        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'familia_id' => $otraFamilia->id]);
    }

    public function test_guardar_en_modo_solo_lectura_devuelve_403(): void
    {
        $admin = $this->admin();
        $familia = FamiliaMaterial::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirVer', $familia->id)
            ->assertSet('modoSoloLectura', true)
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_usuario_con_solo_permiso_ver_no_puede_modificar(): void
    {
        $rolSoloVer = Role::create([
            'name' => 'familias_solo_ver',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rolSoloVer->givePermissionTo('materiales.familias.ver');

        $usuario = User::factory()->administrador()->create();
        $usuario->assignRole($rolSoloVer);

        $familia = FamiliaMaterial::factory()->create();

        Livewire::actingAs($usuario)
            ->test(Index::class)
            ->call('abrirEditar', $familia->id)
            ->assertForbidden();
    }

    public function test_buscador_filtra_por_nombre(): void
    {
        $admin = $this->admin();
        FamiliaMaterial::factory()->create(['nombre' => 'Cables H07V-K']);
        FamiliaMaterial::factory()->create(['nombre' => 'Tubos corrugados']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'cables')
            ->assertSee('Cables H07V-K')
            ->assertDontSee('Tubos corrugados');
    }

    public function test_limpiar_buscador_lo_resetea_e_incrementa_reset_key(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->call('limpiarBuscador')
            ->assertSet('buscar', '')
            ->assertSet('resetKey', 1);
    }
}
