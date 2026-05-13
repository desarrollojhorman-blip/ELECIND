<?php

namespace Tests\Feature\Materiales;

use App\Livewire\Materiales\Index;
use App\Models\Material;
use App\Models\MaterialLote;
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

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.codigo', 'MAT-9999')
            ->set('form.grupo', 'Cableado')
            ->set('form.nombre', 'Cable test')
            ->set('form.unidad_medida', 'm')
            ->set('form.stock_minimo', 10)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('materiales', [
            'codigo' => 'MAT-9999',
            'nombre' => 'Cable test',
            'grupo' => 'Cableado',
        ]);
    }

    public function test_validaciones_obligatorias(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', '')
            ->set('form.unidad_medida', '')
            ->call('guardar')
            ->assertHasErrors([
                'form.nombre' => 'required',
                'form.unidad_medida' => 'required',
            ]);
    }

    public function test_validacion_codigo_unico(): void
    {
        $admin = $this->admin();
        Material::factory()->create(['codigo' => 'DUPLI']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'X')
            ->set('form.codigo', 'DUPLI')
            ->call('guardar')
            ->assertHasErrors(['form.codigo' => 'unique']);
    }

    public function test_un_admin_puede_editar_un_material(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create(['nombre' => 'Original']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $material->id)
            ->assertSet('form.nombre', 'Original')
            ->set('form.nombre', 'Modificado')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'nombre' => 'Modificado',
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
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $material->id);

        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'deleted_at' => null]);
    }

    public function test_stock_total_muestra_la_suma_de_lotes(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create(['nombre' => 'Material con stock']);
        MaterialLote::factory()->create(['material_id' => $material->id, 'stock_disponible' => 50]);
        MaterialLote::factory()->create(['material_id' => $material->id, 'stock_disponible' => 30]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Material con stock')
            ->assertSee('80');
    }

    public function test_filtro_por_grupo(): void
    {
        $admin = $this->admin();
        Material::factory()->create(['nombre' => 'Cable visible', 'grupo' => 'Cableado']);
        Material::factory()->create(['nombre' => 'Bombilla oculta', 'grupo' => 'Iluminación']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('filtroGrupo', 'Cableado')
            ->assertSee('Cable visible')
            ->assertDontSee('Bombilla oculta');
    }

    public function test_limpiar_filtros_resetea_buscador_grupo_y_estado(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'activos')
            ->set('filtroGrupo', 'Cableado')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'todos')
            ->assertSet('filtroGrupo', '')
            ->assertSet('resetKey', 1);
    }
}
