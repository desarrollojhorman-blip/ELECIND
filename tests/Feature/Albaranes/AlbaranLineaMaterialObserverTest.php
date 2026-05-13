<?php

namespace Tests\Feature\Albaranes;

use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Cliente;
use App\Models\MaterialLote;
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

    public function test_crear_linea_descuenta_stock_del_lote(): void
    {
        $lote = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);

        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 15,
        ]);

        $lote->refresh();
        $this->assertEquals(85, $lote->stock_disponible);
    }

    public function test_aumentar_cantidad_de_linea_resta_diff(): void
    {
        $lote = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 10,
        ]);

        $lote->refresh();
        $this->assertEquals(90, $lote->stock_disponible);

        $linea->cantidad = 25;
        $linea->save();

        $lote->refresh();
        $this->assertEquals(75, $lote->stock_disponible);
    }

    public function test_reducir_cantidad_de_linea_devuelve_diff(): void
    {
        $lote = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 30,
        ]);

        $lote->refresh();
        $this->assertEquals(70, $lote->stock_disponible);

        $linea->cantidad = 10;
        $linea->save();

        $lote->refresh();
        $this->assertEquals(90, $lote->stock_disponible);
    }

    public function test_eliminar_linea_devuelve_stock_al_lote(): void
    {
        $lote = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 20,
        ]);

        $lote->refresh();
        $this->assertEquals(80, $lote->stock_disponible);

        $linea->delete();

        $lote->refresh();
        $this->assertEquals(100, $lote->stock_disponible);
    }

    public function test_cambiar_de_lote_devuelve_al_viejo_y_descuenta_del_nuevo(): void
    {
        $loteA = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);
        $loteB = MaterialLote::factory()->create([
            'stock_inicial' => 50,
            'stock_disponible' => 50,
        ]);

        $linea = AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $this->albaran()->id,
            'material_lote_id' => $loteA->id,
            'cantidad' => 30,
        ]);

        $loteA->refresh();
        $loteB->refresh();
        $this->assertEquals(70, $loteA->stock_disponible);
        $this->assertEquals(50, $loteB->stock_disponible);

        $linea->material_lote_id = $loteB->id;
        $linea->cantidad = 10;
        $linea->save();

        $loteA->refresh();
        $loteB->refresh();
        $this->assertEquals(100, $loteA->stock_disponible);
        $this->assertEquals(40, $loteB->stock_disponible);
    }

    public function test_eliminar_albaran_devuelve_todo_el_stock_al_lote(): void
    {
        $lote = MaterialLote::factory()->create([
            'stock_inicial' => 100,
            'stock_disponible' => 100,
        ]);

        $albaran = $this->albaran();

        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $albaran->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 25,
        ]);
        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $albaran->id,
            'material_lote_id' => $lote->id,
            'cantidad' => 15,
        ]);

        $lote->refresh();
        $this->assertEquals(60, $lote->stock_disponible);

        // Eliminamos las líneas (cascade desde albarán haría lo mismo, pero el
        // Observer solo se dispara con .delete() individual del modelo).
        $albaran->lineasMaterial()->each(fn ($l) => $l->delete());

        $lote->refresh();
        $this->assertEquals(100, $lote->stock_disponible);
    }
}
