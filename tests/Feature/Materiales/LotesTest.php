<?php

namespace Tests\Feature\Materiales;

use App\Livewire\Materiales\Lotes;
use App\Models\Material;
use App\Models\MaterialLote;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LotesTest extends TestCase
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

    public function test_un_admin_puede_ver_los_lotes_de_un_material(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();
        MaterialLote::factory()->count(2)->create(['material_id' => $material->id]);

        $response = $this->actingAs($admin)->get(route('materiales.lotes', $material));

        $response->assertOk();
        $response->assertSeeLivewire(Lotes::class);
        $response->assertSee($material->nombre);
    }

    public function test_un_admin_puede_crear_un_lote_para_el_material(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->call('abrirCrear')
            ->set('form.codigo_lote', 'LOT-TEST-001')
            ->set('form.proveedor', 'Proveedor X')
            ->set('form.stock_inicial', 100)
            ->set('form.stock_disponible', 100)
            ->set('form.fecha_entrada', '2026-05-13')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('material_lotes', [
            'material_id' => $material->id,
            'codigo_lote' => 'LOT-TEST-001',
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);
    }

    public function test_validacion_stock_disponible_no_puede_superar_inicial(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->call('abrirCrear')
            ->set('form.stock_inicial', 50)
            ->set('form.stock_disponible', 200)
            ->call('guardar')
            ->assertHasErrors(['form.stock_disponible' => 'lte']);
    }

    public function test_validacion_codigo_lote_unico(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();
        MaterialLote::factory()->create(['material_id' => $material->id, 'codigo_lote' => 'LOT-DUPLI']);

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->call('abrirCrear')
            ->set('form.codigo_lote', 'LOT-DUPLI')
            ->set('form.stock_inicial', 10)
            ->set('form.stock_disponible', 10)
            ->call('guardar')
            ->assertHasErrors(['form.codigo_lote' => 'unique']);
    }

    public function test_validacion_fecha_caducidad_posterior_a_entrada(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->call('abrirCrear')
            ->set('form.stock_inicial', 10)
            ->set('form.stock_disponible', 10)
            ->set('form.fecha_entrada', '2026-05-10')
            ->set('form.fecha_caducidad', '2026-05-01')
            ->call('guardar')
            ->assertHasErrors(['form.fecha_caducidad' => 'after_or_equal']);
    }

    public function test_un_admin_puede_eliminar_y_restaurar_un_lote(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();
        $lote = MaterialLote::factory()->create(['material_id' => $material->id]);

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->call('eliminar', $lote->id);

        $this->assertSoftDeleted('material_lotes', ['id' => $lote->id]);

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $lote->id);

        $this->assertDatabaseHas('material_lotes', ['id' => $lote->id, 'deleted_at' => null]);
    }

    public function test_filtro_con_stock_y_sin_stock(): void
    {
        $admin = $this->admin();
        $material = Material::factory()->create();
        MaterialLote::factory()->create(['material_id' => $material->id, 'codigo_lote' => 'LOT-LLENO', 'stock_disponible' => 10]);
        MaterialLote::factory()->create(['material_id' => $material->id, 'codigo_lote' => 'LOT-VACIO', 'stock_disponible' => 0]);

        Livewire::actingAs($admin)
            ->test(Lotes::class, ['material' => $material])
            ->set('filtroEstado', 'sin_stock')
            ->assertSee('LOT-VACIO')
            ->assertDontSee('LOT-LLENO');
    }
}
