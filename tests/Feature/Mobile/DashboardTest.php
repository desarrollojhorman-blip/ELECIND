<?php

namespace Tests\Feature\Mobile;

use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    private function administrador(): User
    {
        $user = User::factory()->administrador()->create();
        $user->assignRole('administrador');

        return $user;
    }

    private function superadmin(): User
    {
        $user = User::factory()->administrador()->create();
        $user->assignRole('superadmin');

        return $user;
    }

    private function responsable(int $clienteId): User
    {
        $user = User::factory()->responsableDe($clienteId)->create();
        $user->assignRole('responsable');

        return $user;
    }

    public function test_un_trabajador_puede_acceder_al_dashboard_movil(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('mobile.dashboard'));

        $response->assertOk();
        $response->assertSee('Parte de Trabajo');
        $response->assertSee('Parte personalizado');
        $response->assertSee('Gestión de Albaranes');
        $response->assertSee('Faltas de Asistencia');
        $response->assertSee('Resumen mensual');
    }

    public function test_un_administrador_sin_acceso_movil_es_redirigido_al_login(): void
    {
        // administrador solo tiene acceso='web' en el rol
        $response = $this->actingAs($this->administrador())->get(route('mobile.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_un_superadmin_con_acceso_ambos_puede_ver_el_dashboard_movil(): void
    {
        $response = $this->actingAs($this->superadmin())->get(route('mobile.dashboard'));

        $response->assertOk();
        $response->assertSee('Parte de Trabajo');
    }

    public function test_un_responsable_externo_puede_acceder_al_dashboard_movil(): void
    {
        $cliente = Cliente::factory()->create();
        $response = $this->actingAs($this->responsable($cliente->id))->get(route('mobile.dashboard'));

        $response->assertOk();
    }

    public function test_el_dashboard_muestra_el_menu_de_acciones_y_cerrar_sesion(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('mobile.dashboard'));

        $response->assertSee('Nueva incidencia');
        $response->assertSee('Cerrar sesión');
    }

    public function test_el_menu_cambiar_a_panel_web_solo_aparece_si_el_usuario_tiene_acceso_web(): void
    {
        // Trabajador: solo móvil → NO debe aparecer
        $response = $this->actingAs($this->trabajador())->get(route('mobile.dashboard'));
        $response->assertDontSee('Cambiar a panel web');

        // Superadmin: ambos → SÍ debe aparecer
        $response = $this->actingAs($this->superadmin())->get(route('mobile.dashboard'));
        $response->assertSee('Cambiar a panel web');
    }

    public function test_las_rutas_aun_placeholder_responden_ok_para_un_trabajador(): void
    {
        $trabajador = $this->trabajador();

        // Solo quedan 3 rutas como placeholder (el resto ya está construido en Iter. 3+).
        $rutas = [
            'mobile.albaranes.personalizado',
            'mobile.ausencias.index',
            'mobile.resumen.index',
            'mobile.incidencias.nueva',
        ];

        foreach ($rutas as $ruta) {
            $response = $this->actingAs($trabajador)->get(route($ruta));
            $response->assertOk();
            $response->assertSee('En construcción');
        }
    }

    public function test_las_placeholders_muestran_la_etiqueta_del_roadmap(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('mobile.albaranes.personalizado'));
        $response->assertSee('Iter. 3.5');

        $response = $this->actingAs($this->trabajador())->get(route('mobile.ausencias.index'));
        $response->assertSee('Fase 4');

        $response = $this->actingAs($this->trabajador())->get(route('mobile.resumen.index'));
        $response->assertSee('Fase 5');
    }
}
