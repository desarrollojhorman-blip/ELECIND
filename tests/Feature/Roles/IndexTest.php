<?php

namespace Tests\Feature\Roles;

use App\Livewire\Roles\Index;
use App\Models\Permission;
use App\Models\Role;
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

    private function superadmin(): User
    {
        $u = User::factory()->superadmin()->create();
        $u->assignRole('superadmin');

        return $u;
    }

    private function admin(): User
    {
        $u = User::factory()->administrador()->create();
        $u->assignRole('administrador');

        return $u;
    }

    private function trabajador(): User
    {
        $u = User::factory()->trabajador()->create();
        $u->assignRole('trabajador');

        return $u;
    }

    public function test_un_admin_puede_acceder_a_la_pantalla_de_roles(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('configuracion.roles'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_no_pasa_el_middleware_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('configuracion.roles'));

        $response->assertRedirect('/login');
    }

    public function test_admin_no_ve_el_rol_superadmin_en_el_listado(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertDontSee('superadmin');
    }

    public function test_superadmin_ve_todos_los_roles(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->assertSee('superadmin')
            ->assertSee('administrador')
            ->assertSee('trabajador')
            ->assertSee('responsable');
    }

    public function test_superadmin_puede_crear_un_rol_personalizado(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'supervisor_obra')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 30)
            ->set('form.permisos', Permission::whereIn('name', ['clientes.ver', 'proyectos.ver'])->pluck('id')->all())
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('roles', [
            'name' => 'supervisor_obra',
            'acceso' => 'web',
            'nivel' => 30,
            'es_sistema' => 0,
        ]);

        $rol = Role::where('name', 'supervisor_obra')->first();
        $this->assertCount(2, $rol->permissions);
    }

    public function test_admin_no_puede_asignar_ambito_ambos(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'rol_mixto')
            ->set('form.acceso', 'ambos')
            ->set('form.nivel', 30)
            ->call('guardar')
            ->assertHasErrors(['form.acceso']);

        $this->assertDatabaseMissing('roles', ['name' => 'rol_mixto']);
    }

    public function test_no_se_puede_asignar_nivel_superior_al_propio(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'super_rol')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 99)
            ->call('guardar')
            ->assertHasErrors(['form.nivel']);

        $this->assertDatabaseMissing('roles', ['name' => 'super_rol']);
    }

    public function test_admin_solo_puede_delegar_permisos_que_tiene(): void
    {
        $admin = $this->admin();
        // El permiso usuarios.crear_superadmin solo lo tiene superadmin.
        $permisoVedado = Permission::where('name', 'usuarios.crear_superadmin')->first();
        $permisoOk = Permission::where('name', 'clientes.ver')->first();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'rol_test')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->set('form.permisos', [$permisoVedado->id, $permisoOk->id])
            ->call('guardar')
            ->assertHasNoErrors();

        $rol = Role::where('name', 'rol_test')->first();
        $nombres = $rol->permissions->pluck('name')->all();

        // Solo se guardó el permiso que el admin sí tiene.
        $this->assertContains('clientes.ver', $nombres);
        $this->assertNotContains('usuarios.crear_superadmin', $nombres);
    }

    public function test_permisos_movil_no_se_guardan_en_rol_web(): void
    {
        $superadmin = $this->superadmin();

        $permisoMovil = Permission::where('name', 'albaranes.crear_movil')->first();
        $permisoWeb = Permission::where('name', 'clientes.ver')->first();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'rol_web_only')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->set('form.permisos', [$permisoMovil->id, $permisoWeb->id])
            ->call('guardar')
            ->assertHasNoErrors();

        $rol = Role::where('name', 'rol_web_only')->first();
        $nombres = $rol->permissions->pluck('name')->all();

        $this->assertContains('clientes.ver', $nombres);
        $this->assertNotContains('albaranes.crear_movil', $nombres);
    }

    public function test_nombre_invalido_falla_validacion(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'Rol Con Espacios')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->call('guardar')
            ->assertHasErrors(['form.name']);
    }

    public function test_nombre_duplicado_falla_validacion(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.name', 'administrador')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->call('guardar')
            ->assertHasErrors(['form.name']);
    }

    public function test_no_se_puede_eliminar_un_rol_del_sistema(): void
    {
        $superadmin = $this->superadmin();
        $administradorRol = Role::where('name', 'administrador')->first();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('eliminar', $administradorRol->id)
            ->assertForbidden();

        $this->assertDatabaseHas('roles', ['name' => 'administrador']);
    }

    public function test_se_puede_eliminar_un_rol_personalizado(): void
    {
        $superadmin = $this->superadmin();
        $rolCustom = Role::create([
            'name' => 'rol_para_borrar',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('eliminar', $rolCustom->id);

        $this->assertDatabaseMissing('roles', ['name' => 'rol_para_borrar']);
    }

    public function test_admin_no_puede_editar_un_rol_del_sistema(): void
    {
        $admin = $this->admin();
        $trabajadorRol = Role::where('name', 'trabajador')->first();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirEditar', $trabajadorRol->id)
            ->assertForbidden();
    }

    public function test_superadmin_puede_editar_un_rol_del_sistema_pero_no_renombrarlo(): void
    {
        $superadmin = $this->superadmin();
        $administradorRol = Role::where('name', 'administrador')->first();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $administradorRol->id)
            ->set('form.name', 'administrador_renombrado')
            ->call('guardar')
            ->assertHasNoErrors();

        // El nombre NO se cambió pese al intento.
        $this->assertDatabaseHas('roles', ['name' => 'administrador']);
        $this->assertDatabaseMissing('roles', ['name' => 'administrador_renombrado']);
    }

    public function test_nadie_puede_editar_el_rol_superadmin(): void
    {
        $superadmin = $this->superadmin();
        $superadminRol = Role::where('name', 'superadmin')->first();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $superadminRol->id)
            ->assertForbidden();
    }

    public function test_cambiar_ambito_con_permisos_previos_abre_modal_de_confirmacion(): void
    {
        $superadmin = $this->superadmin();
        $rol = Role::create([
            'name' => 'rol_para_cambiar',
            'guard_name' => 'web',
            'nivel' => 20,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rol->syncPermissions(Permission::where('name', 'clientes.ver')->get());

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $rol->id)
            ->set('form.acceso', 'movil')
            ->assertSet('modalCambioAmbitoAbierto', true)
            ->assertSet('cantidadPermisosAfectados', 1);
    }

    public function test_confirmar_cambio_ambito_resetea_permisos(): void
    {
        $superadmin = $this->superadmin();
        $rol = Role::create([
            'name' => 'rol_cambiar_2',
            'guard_name' => 'web',
            'nivel' => 20,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rol->syncPermissions(Permission::where('name', 'clientes.ver')->get());

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $rol->id)
            ->set('form.acceso', 'movil')
            ->call('confirmarCambioAmbito')
            ->assertSet('form.permisos', [])
            ->assertSet('modalCambioAmbitoAbierto', false)
            ->assertSet('form.acceso', 'movil');
    }

    public function test_cancelar_cambio_ambito_revierte_al_anterior(): void
    {
        $superadmin = $this->superadmin();
        $rol = Role::create([
            'name' => 'rol_cambiar_3',
            'guard_name' => 'web',
            'nivel' => 20,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $rol->syncPermissions(Permission::where('name', 'clientes.ver')->get());

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $rol->id)
            ->set('form.acceso', 'movil')
            ->call('cancelarCambioAmbito')
            ->assertSet('form.acceso', 'web')
            ->assertSet('modalCambioAmbitoAbierto', false);
    }

    public function test_filtro_tipo_personalizados_oculta_los_de_sistema(): void
    {
        $superadmin = $this->superadmin();
        Role::create([
            'name' => 'rol_visible',
            'guard_name' => 'web',
            'nivel' => 10,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('filtroTipo', 'personalizados')
            ->assertSee('rol_visible')
            ->assertDontSee('administrador');
    }

    public function test_un_trabajador_no_tiene_acceso_aunque_intente_ir_directo(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('configuracion.roles'));

        $response->assertRedirect('/login');
    }

    public function test_toggle_categoria_marca_todos_los_permisos_de_esa_categoria(): void
    {
        $superadmin = $this->superadmin();

        $component = Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->call('toggleCategoria', 'clientes');

        $idsClientes = Permission::where('categoria', 'clientes')
            ->whereIn('ambito', ['web', 'ambos'])
            ->pluck('id')
            ->map(fn ($i) => (int) $i)
            ->all();

        $seleccionados = $component->get('form.permisos');
        foreach ($idsClientes as $id) {
            $this->assertContains($id, $seleccionados);
        }
    }

    public function test_toggle_categoria_estando_todos_marcados_desmarca_todos(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20)
            ->call('toggleCategoria', 'clientes')
            ->call('toggleCategoria', 'clientes')
            ->assertSet('form.permisos', []);
    }

    public function test_estado_categoria_devuelve_all_some_none_correctamente(): void
    {
        $superadmin = $this->superadmin();
        $idsClientes = Permission::where('categoria', 'clientes')
            ->whereIn('ambito', ['web', 'ambos'])
            ->pluck('id')
            ->map(fn ($i) => (int) $i)
            ->all();

        $component = Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.acceso', 'web')
            ->set('form.nivel', 20);

        $this->assertSame('none', $component->instance()->estadoCategoria('clientes'));

        // Marca uno solo manualmente.
        $component->set('form.permisos', [$idsClientes[0]]);
        $this->assertSame('some', $component->instance()->estadoCategoria('clientes'));

        // Marca todos.
        $component->set('form.permisos', $idsClientes);
        $this->assertSame('all', $component->instance()->estadoCategoria('clientes'));
    }

    public function test_permisos_agrupados_ordena_dentro_de_cada_categoria_web_movil_ambos(): void
    {
        $superadmin = $this->superadmin();

        $component = Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.acceso', 'ambos')
            ->set('form.nivel', 50);

        $agrupados = $component->instance()->permisosAgrupados;

        $this->assertArrayHasKey('albaranes', $agrupados);

        $ordenEsperado = ['web' => 1, 'movil' => 2, 'ambos' => 3];
        foreach ($agrupados as $categoria => $permisos) {
            $prev = 0;
            foreach ($permisos as $permiso) {
                $rank = $ordenEsperado[$permiso->ambito] ?? 99;
                $this->assertGreaterThanOrEqual(
                    $prev,
                    $rank,
                    "Permiso «{$permiso->name}» (ámbito: {$permiso->ambito}) en categoría «{$categoria}» rompe el orden web → movil → ambos."
                );
                $prev = $rank;
            }
        }
    }

    public function test_un_admin_sin_permiso_roles_gestionar_recibe_403(): void
    {
        // Creamos un user con un rol custom que tiene acceso web pero NO roles.gestionar.
        $rol = Role::create([
            'name' => 'sin_roles_gestionar',
            'guard_name' => 'web',
            'nivel' => 30,
            'acceso' => 'web',
            'es_sistema' => false,
        ]);
        $sinPermiso = User::factory()->administrador()->create();
        $sinPermiso->assignRole($rol);

        $response = $this->actingAs($sinPermiso)->get(route('configuracion.roles'));

        $response->assertForbidden();
    }
}
