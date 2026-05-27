<?php

namespace Tests\Feature\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Livewire\Mobile\Albaranes\Ver;
use App\Models\Albaran;
use App\Models\AlbaranLineaMaterial;
use App\Models\Cliente;
use App\Models\Material;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function trabajador(): User
    {
        $user = User::factory()->trabajador()->create();
        $user->assignRole('trabajador');

        return $user;
    }

    public function test_un_trabajador_puede_ver_un_albaran_propio(): void
    {
        $yo = $this->trabajador();
        $cliente = Cliente::factory()->create();
        $albaran = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
            'numero' => 'ALB-VISTA',
        ]);

        $response = $this->actingAs($yo)->get(route('mobile.albaranes.ver', ['albaran' => $albaran->getKey()]));

        $response->assertOk();
        $response->assertSee('ALB-VISTA');
        $response->assertSeeLivewire(Ver::class);
    }

    public function test_un_trabajador_no_puede_ver_un_albaran_ajeno(): void
    {
        $yo = $this->trabajador();
        $otro = $this->trabajador();
        $cliente = Cliente::factory()->create();
        $albaran = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $otro->getKey(),
        ]);

        $response = $this->actingAs($yo)->get(route('mobile.albaranes.ver', ['albaran' => $albaran->getKey()]));

        $response->assertForbidden();
    }

    public function test_eliminar_un_albaran_pendiente_devuelve_el_stock_al_material(): void
    {
        $yo = $this->trabajador();
        $cliente = Cliente::factory()->create();
        $albaran = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
            'estado' => EstadoAlbaran::PENDIENTE_FIRMA,
        ]);

        $material = Material::factory()->create(['stock' => 80]);

        AlbaranLineaMaterial::factory()->create([
            'albaran_id' => $albaran->id,
            'material_id' => $material->id,
            'cantidad' => 20,
        ]);

        // El factory de línea descuenta 20 vía Observer → material queda en 60.
        $this->assertSame('60.00', (string) $material->fresh()->stock);

        Livewire::actingAs($yo)
            ->test(Ver::class, ['albaran' => $albaran])
            ->call('eliminar');

        $this->assertSoftDeleted('albaranes', ['id' => $albaran->id]);
        $this->assertSame('80.00', (string) $material->fresh()->stock);
    }

    public function test_no_se_puede_eliminar_un_albaran_firmado_desde_movil(): void
    {
        $yo = $this->trabajador();
        $cliente = Cliente::factory()->create();
        $albaran = Albaran::factory()->firmado()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
        ]);

        Livewire::actingAs($yo)
            ->test(Ver::class, ['albaran' => $albaran])
            ->call('eliminar')
            ->assertForbidden();
    }
}
