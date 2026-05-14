<?php

namespace Tests\Feature\Albaranes;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\Proyecto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlbaranLineaMaterialObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function albaran(): Albaran
    {
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create(['cliente_id' => $cliente->id]);
        $user = User::factory()->trabajador()->create();

        return Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'proyecto_id' => $proyecto->id,
            'creado_por' => $user->id,
        ]);
    }

    public function test_crear_linea_descuenta_stock_del_material(): void
    {
        $material = Material::factory()->create(['stock' => 100]);

        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_id' => $material->id,
            'cantidad' => 15,
        ]);

        $material->refresh();
        $this->assertEquals(85, $material->stock);
    }

    public function test_aumentar_cantidad_de_linea_resta_diff(): void
    {
        $material = Material::factory()->create(['stock' => 100]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_id' => $material->id,
            'cantidad' => 10,
        ]);

        $material->refresh();
        $this->assertEquals(90, $material->stock);

        $linea->cantidad = 25;
        $linea->save();

        $material->refresh();
        $this->assertEquals(75, $material->stock);
    }

    public function test_reducir_cantidad_de_linea_devuelve_diff(): void
    {
        $material = Material::factory()->create(['stock' => 100]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_id' => $material->id,
            'cantidad' => 30,
        ]);

        $material->refresh();
        $this->assertEquals(70, $material->stock);

        $linea->cantidad = 10;
        $linea->save();

        $material->refresh();
        $this->assertEquals(90, $material->stock);
    }

    public function test_eliminar_linea_devuelve_stock_al_material(): void
    {
        $material = Material::factory()->create(['stock' => 100]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_id' => $material->id,
            'cantidad' => 20,
        ]);

        $material->refresh();
        $this->assertEquals(80, $material->stock);

        $linea->delete();

        $material->refresh();
        $this->assertEquals(100, $material->stock);
    }

    public function test_cambiar_de_material_devuelve_al_viejo_y_descuenta_del_nuevo(): void
    {
        $materialA = Material::factory()->create(['stock' => 100]);
        $materialB = Material::factory()->create(['stock' => 50]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_id' => $materialA->id,
            'cantidad' => 30,
        ]);

        $materialA->refresh();
        $materialB->refresh();
        $this->assertEquals(70, $materialA->stock);
        $this->assertEquals(50, $materialB->stock);

        $linea->material_id = $materialB->id;
        $linea->cantidad = 10;
        $linea->save();

        $materialA->refresh();
        $materialB->refresh();
        $this->assertEquals(100, $materialA->stock);
        $this->assertEquals(40, $materialB->stock);
    }

    public function test_eliminar_albaran_devuelve_todo_el_stock_al_material(): void
    {
        $material = Material::factory()->create(['stock' => 100]);

        $albaran = $this->albaran();

        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $albaran->id,
            'material_id' => $material->id,
            'cantidad' => 25,
        ]);
        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $albaran->id,
            'material_id' => $material->id,
            'cantidad' => 15,
        ]);

        $material->refresh();
        $this->assertEquals(60, $material->stock);

        // Eliminamos las líneas (cascade desde albarán haría lo mismo, pero el
        // Observer solo se dispara con .delete() individual del modelo).
        $albaran->lineasMaterial()->each(fn ($l) => $l->delete());

        $material->refresh();
        $this->assertEquals(100, $material->stock);
    }
}
