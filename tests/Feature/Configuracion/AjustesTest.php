<?php

namespace Tests\Feature\Configuracion;

use App\Livewire\Configuracion\Ajustes;
use App\Models\User;
use App\Support\Branding;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AjustesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        Branding::limpiarCache();
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

    public function test_un_admin_puede_acceder_a_la_pantalla_de_ajustes(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('configuracion.ajustes'));

        $response->assertOk();
        $response->assertSeeLivewire(Ajustes::class);
    }

    public function test_un_trabajador_es_redirigido_por_no_tener_acceso_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('configuracion.ajustes'));

        $response->assertRedirect('/login');
    }

    public function test_validacion_color_primario_debe_ser_hex_valido(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Ajustes::class)
            ->set('color_primario', 'azul')
            ->call('guardar')
            ->assertHasErrors(['color_primario']);
    }

    public function test_validacion_token_caducidad_dias_entre_1_y_90(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Ajustes::class)
            ->set('token_caducidad_dias', 0)
            ->call('guardar')
            ->assertHasErrors(['token_caducidad_dias']);

        Livewire::actingAs($admin)
            ->test(Ajustes::class)
            ->set('token_caducidad_dias', 91)
            ->call('guardar')
            ->assertHasErrors(['token_caducidad_dias']);
    }

    public function test_un_admin_puede_guardar_ajustes_validos(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Ajustes::class)
            ->set('plantilla_numeracion_albaran', 'ALB-{YYYY}-{NNNN}')
            ->set('token_caducidad_dias', 30)
            ->set('color_primario', '#1d4ed8')
            ->set('color_secundario', '#dbeafe')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('empresa', [
            'plantilla_numeracion_albaran' => 'ALB-{YYYY}-{NNNN}',
            'token_caducidad_dias' => 30,
            'color_primario' => '#1d4ed8',
            'color_secundario' => '#dbeafe',
        ]);
    }

    public function test_branding_helper_devuelve_colores_configurados(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Ajustes::class)
            ->set('color_primario', '#0ea5e9')
            ->set('color_secundario', '#e0f2fe')
            ->call('guardar');

        Branding::limpiarCache();

        $this->assertSame('#0ea5e9', Branding::colorPrimario());
        $this->assertSame('#e0f2fe', Branding::colorSecundario());
    }
}
