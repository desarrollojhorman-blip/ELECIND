<?php

namespace Tests\Feature\Proyectos;

use App\Livewire\Proyectos\Grupos\Index;
use App\Models\Proyecto;
use App\Models\TiposProyecto;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GruposIndexTest extends TestCase
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
        $trabajador = User::factory()->trabajador()->create();
        $trabajador->assignRole('trabajador');

        return $trabajador;
    }

    public function test_un_admin_puede_ver_grupo_proyectos(): void
    {
        $response = $this->actingAs($this->admin())->get(route('proyectos.grupos'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login(): void
    {
        $response = $this->actingAs($this->trabajador())->get(route('proyectos.grupos'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_puede_crear_un_grupo_proyecto(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Marzo 2026')
            ->set('form.descripcion', 'Grupo para obras de marzo')
            ->set('form.activo', true)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('tipos_proyectos', [
            'nombre' => 'Marzo 2026',
            'descripcion' => 'Grupo para obras de marzo',
            'activo' => true,
        ]);
    }

    public function test_validacion_grupo_nombre_obligatorio(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', '')
            ->call('guardar')
            ->assertHasErrors(['form.nombre' => 'required']);
    }

    public function test_editar_grupo_permite_asignar_un_proyecto_sin_grupo(): void
    {
        $admin = $this->admin();
        $grupo = TiposProyecto::factory()->create();
        $proyecto = Proyecto::factory()->create(['tipo_proyecto_id' => null]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $grupo->id)
            ->set('proyectoAAsignar', $proyecto->id)
            ->call('agregarProyectoAGrupo')
            ->assertSet('proyectoAAsignar', null);

        $this->assertDatabaseHas('proyectos', [
            'id' => $proyecto->id,
            'tipo_proyecto_id' => $grupo->id,
        ]);
    }
}
