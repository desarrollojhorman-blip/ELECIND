<?php

namespace Tests\Feature\Mobile\Albaranes;

use App\Enums\EstadoAlbaran;
use App\Livewire\Mobile\Albaranes\Index;
use App\Models\Albaran;
use App\Models\AlbaranLineaPersonal;
use App\Models\Cliente;
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

    private function trabajador(): User
    {
        $user = User::factory()->trabajador()->create();
        $user->assignRole('trabajador');

        return $user;
    }

    public function test_un_trabajador_solo_ve_sus_propios_albaranes(): void
    {
        $yo = $this->trabajador();
        $otro = $this->trabajador();
        $cliente = Cliente::factory()->create();

        $mio = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
            'numero' => 'ALB-MIO',
        ]);
        $delOtro = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $otro->getKey(),
            'numero' => 'ALB-OTRO',
        ]);

        Livewire::actingAs($yo)
            ->test(Index::class)
            ->assertSee('ALB-MIO')
            ->assertDontSee('ALB-OTRO');
    }

    public function test_un_trabajador_ve_los_albaranes_donde_es_companero_aunque_no_los_creara(): void
    {
        $yo = $this->trabajador();
        $otro = $this->trabajador();
        $cliente = Cliente::factory()->create();

        $albaran = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $otro->getKey(),
            'numero' => 'ALB-COMPANERO',
        ]);

        AlbaranLineaPersonal::factory()->create([
            'albaran_id' => $albaran->id,
            'trabajador_id' => $yo->getKey(),
        ]);

        Livewire::actingAs($yo)
            ->test(Index::class)
            ->assertSee('ALB-COMPANERO');
    }

    public function test_filtro_por_estado_pendiente_firma(): void
    {
        $yo = $this->trabajador();
        $cliente = Cliente::factory()->create();

        Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
            'numero' => 'ALB-PEND',
            'estado' => EstadoAlbaran::PENDIENTE_FIRMA,
        ]);
        Albaran::factory()->firmado()->create([
            'cliente_id' => $cliente->id,
            'creado_por' => $yo->getKey(),
            'numero' => 'ALB-FIRM',
        ]);

        Livewire::actingAs($yo)
            ->test(Index::class)
            ->call('setFiltro', 'pendiente_firma')
            ->assertSee('ALB-PEND')
            ->assertDontSee('ALB-FIRM');
    }
}
