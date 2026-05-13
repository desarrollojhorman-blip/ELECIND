<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_superadmin_pasa_el_middleware_web(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)->get('/')->assertOk();
    }

    public function test_administrador_pasa_el_middleware_web(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrador');

        $this->actingAs($user)->get('/')->assertOk();
    }

    public function test_trabajador_no_pasa_el_middleware_web(): void
    {
        $user = User::factory()->create();
        $user->assignRole('trabajador');

        $this->actingAs($user)->get('/')->assertRedirect('/login');
    }

    public function test_responsable_no_pasa_el_middleware_web(): void
    {
        $user = User::factory()->create();
        $user->assignRole('responsable');

        $this->actingAs($user)->get('/')->assertRedirect('/login');
    }

    public function test_usuario_sin_rol_no_pasa_el_middleware_web(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertRedirect('/login');
    }

    public function test_user_helper_tiene_acceso_web_es_true_para_rol_ambos(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        $user->refresh();

        $this->assertTrue($user->tieneAccesoWeb());
        $this->assertTrue($user->tieneAccesoMovil());
    }

    public function test_user_helper_tiene_acceso_movil_es_true_para_rol_trabajador(): void
    {
        $user = User::factory()->create();
        $user->assignRole('trabajador');
        $user->refresh();

        $this->assertTrue($user->tieneAccesoMovil());
        $this->assertFalse($user->tieneAccesoWeb());
    }

    public function test_user_helper_tiene_acceso_web_es_true_para_rol_administrador(): void
    {
        $user = User::factory()->create();
        $user->assignRole('administrador');
        $user->refresh();

        $this->assertTrue($user->tieneAccesoWeb());
        $this->assertFalse($user->tieneAccesoMovil());
    }

    public function test_un_rol_custom_con_acceso_web_pasa_el_middleware(): void
    {
        Role::create([
            'name' => 'supervisor_custom',
            'guard_name' => 'web',
            'nivel' => 30,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);

        $user = User::factory()->create();
        $user->assignRole('supervisor_custom');

        $this->actingAs($user)->get('/')->assertOk();
    }
}
