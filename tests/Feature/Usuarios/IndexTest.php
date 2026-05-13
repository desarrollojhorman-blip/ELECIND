<?php

namespace Tests\Feature\Usuarios;

use App\Livewire\Forms\UserForm;
use App\Livewire\Usuarios\Index;
use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_un_superadmin_puede_ver_el_listado_de_usuarios(): void
    {
        $superadmin = $this->superadmin();

        $response = $this->actingAs($superadmin)->get(route('usuarios.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
    }

    public function test_un_trabajador_es_redirigido_al_login_por_no_tener_acceso_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('usuarios.index'));

        $response->assertRedirect('/login');
    }

    public function test_un_admin_no_ve_usuarios_de_nivel_superior(): void
    {
        $admin = $this->admin();
        $superadmin = $this->superadmin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertDontSee($superadmin->username);
    }

    public function test_un_superadmin_ve_a_todos(): void
    {
        $superadmin = $this->superadmin();
        $admin = $this->admin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->assertSee($admin->username);
    }

    public function test_un_superadmin_puede_crear_un_usuario_interno(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Juan')
            ->set('form.apellidos', 'Pérez')
            ->set('form.username', 'juan')
            ->set('form.email', 'juan@elecind.test')
            ->set('form.rol', 'trabajador')
            ->set('form.tipo_usuario', 'interno')
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('users', [
            'username' => 'juan',
            'nombre' => 'Juan',
            'tipo_usuario' => 'interno',
        ]);

        $creado = User::where('username', 'juan')->first();
        $this->assertTrue($creado->hasRole('trabajador'));
    }

    public function test_validacion_username_obligatorio(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Juan')
            ->set('form.username', '')
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertHasErrors(['form.username' => 'required']);
    }

    public function test_sugerencia_username_genera_slug_unico(): void
    {
        User::factory()->create(['username' => 'juan']);
        User::factory()->create(['username' => 'juan.2']);

        $sugerencia = UserForm::sugerirUsername('Juan', 'Pérez');

        $this->assertSame('juan.3', $sugerencia);
    }

    public function test_autosugerencia_se_aplica_al_escribir_nombre_en_alta(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'María')
            ->assertSet('form.username', 'maria');
    }

    public function test_autosugerencia_no_pisa_un_username_manual(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.username', 'admin.especial')
            ->set('form.nombre', 'María')
            ->assertSet('form.username', 'admin.especial');
    }

    public function test_email_duplicado_muestra_modal_y_no_guarda(): void
    {
        $superadmin = $this->superadmin();
        User::factory()->create(['email' => 'colision@elecind.test', 'nombre' => 'Existente']);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Nuevo')
            ->set('form.username', 'nuevo')
            ->set('form.email', 'colision@elecind.test')
            ->set('form.rol', 'trabajador')
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertSet('modalDuplicadosAbierto', true)
            ->assertSet('modalAbierto', true);

        $this->assertDatabaseMissing('users', ['username' => 'nuevo']);
    }

    public function test_confirmar_crear_aunque_duplicado_guarda_de_todas_formas(): void
    {
        $superadmin = $this->superadmin();
        User::factory()->create(['email' => 'colision@elecind.test', 'nombre' => 'Existente']);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Nuevo')
            ->set('form.username', 'nuevo.dup')
            ->set('form.email', 'colision@elecind.test')
            ->set('form.rol', 'trabajador')
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertSet('modalDuplicadosAbierto', true)
            ->call('confirmarCrearAunqueDuplicado')
            ->assertHasNoErrors()
            ->assertSet('modalAbierto', false);

        $this->assertDatabaseHas('users', ['username' => 'nuevo.dup']);
        $this->assertSame(2, User::where('email', 'colision@elecind.test')->count());
    }

    public function test_usar_existente_abre_el_duplicado_en_edicion(): void
    {
        $superadmin = $this->superadmin();
        $existente = User::factory()->create([
            'email' => 'colision@elecind.test',
            'nombre' => 'Existente',
            'username' => 'existente',
        ]);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Nuevo')
            ->set('form.username', 'nuevo')
            ->set('form.email', 'colision@elecind.test')
            ->set('form.rol', 'trabajador')
            ->set('form.password', 'password')
            ->call('guardar')
            ->call('usarExistente', $existente->id)
            ->assertSet('modalDuplicadosAbierto', false)
            ->assertSet('modalAbierto', true)
            ->assertSet('form.id', $existente->id)
            ->assertSet('form.username', 'existente');
    }

    public function test_password_debe_tener_longitud_minima(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Juan')
            ->set('form.username', 'juan')
            ->set('form.rol', 'trabajador')
            ->set('form.password', '12345')
            ->call('guardar')
            ->assertHasErrors(['form.password' => 'min']);
    }

    public function test_editar_sin_password_mantiene_la_anterior(): void
    {
        $superadmin = $this->superadmin();
        $usuario = User::factory()->create([
            'username' => 'preexistente',
            'password' => Hash::make('antigua'),
        ]);
        $usuario->assignRole('trabajador');

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirEditar', $usuario->id)
            ->set('form.nombre', 'Renombrado')
            ->call('guardar')
            ->assertHasNoErrors();

        $usuario->refresh();
        $this->assertSame('Renombrado', $usuario->nombre);
        $this->assertTrue(Hash::check('antigua', $usuario->password));
    }

    public function test_externo_requiere_empresa_cliente(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Responsable Test')
            ->set('form.username', 'resp.test')
            ->set('form.rol', 'responsable')
            ->set('form.tipo_usuario', 'externo')
            ->set('form.cliente_id', null)
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertHasErrors(['form.cliente_id' => 'required']);
    }

    public function test_externo_con_empresa_se_guarda_correctamente(): void
    {
        $superadmin = $this->superadmin();
        $empresa = Cliente::factory()->create();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Responsable Test')
            ->set('form.username', 'resp.test')
            ->set('form.rol', 'responsable')
            ->set('form.tipo_usuario', 'externo')
            ->set('form.cliente_id', $empresa->id)
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'resp.test',
            'tipo_usuario' => 'externo',
            'cliente_id' => $empresa->id,
        ]);
    }

    public function test_un_admin_no_puede_asignar_rol_superadmin(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('abrirCrear')
            ->set('form.nombre', 'Intento')
            ->set('form.username', 'intento')
            ->set('form.rol', 'superadmin')
            ->set('form.password', 'password')
            ->call('guardar')
            ->assertHasErrors(['form.rol']);

        $this->assertDatabaseMissing('users', ['username' => 'intento']);
    }

    public function test_un_admin_puede_eliminar_y_un_superadmin_restaurar(): void
    {
        $admin = $this->admin();
        $superadmin = $this->superadmin();

        $usuario = User::factory()->create();
        $usuario->assignRole('trabajador');

        // El admin tiene usuarios.modificar pero NO usuarios.eliminar → 403.
        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('eliminar', $usuario->id)
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'deleted_at' => null]);

        // El superadmin sí puede eliminar y restaurar.
        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('eliminar', $usuario->id);

        $this->assertSoftDeleted('users', ['id' => $usuario->id]);

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('filtroEstado', 'papelera')
            ->call('restaurar', $usuario->id);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'deleted_at' => null]);
    }

    public function test_nadie_puede_eliminarse_a_si_mismo(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->call('eliminar', $superadmin->id)
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $superadmin->id, 'deleted_at' => null]);
    }

    public function test_filtro_por_rol_solo_muestra_ese_rol(): void
    {
        $superadmin = $this->superadmin();
        $admin = $this->admin();
        $trabajador = User::factory()->create(['nombre' => 'Trabajador X', 'username' => 'trab.x']);
        $trabajador->assignRole('trabajador');

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('filtroRol', 'trabajador')
            ->assertSee('trab.x')
            ->assertDontSee($admin->username);
    }

    public function test_buscador_filtra_por_username_nombre_email_dni(): void
    {
        $superadmin = $this->superadmin();
        $u = User::factory()->create(['username' => 'objetivo', 'nombre' => 'Objetivo']);
        $u->assignRole('trabajador');

        $otro = User::factory()->create(['username' => 'distinto', 'nombre' => 'Distinto']);
        $otro->assignRole('trabajador');

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('buscar', 'Objetivo')
            ->assertSee('objetivo')
            ->assertDontSee('distinto');
    }

    public function test_limpiar_filtros_resetea_todo_e_incrementa_reset_key(): void
    {
        $superadmin = $this->superadmin();

        Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('buscar', 'algo')
            ->set('filtroEstado', 'inactivos')
            ->set('filtroTipo', 'externo')
            ->set('filtroRol', 'trabajador')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSet('filtroEstado', 'activos')
            ->assertSet('filtroTipo', null)
            ->assertSet('filtroRol', null)
            ->assertSet('resetKey', 1);
    }
}
