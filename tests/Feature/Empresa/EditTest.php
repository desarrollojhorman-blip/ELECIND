<?php

namespace Tests\Feature\Empresa;

use App\Livewire\Empresa\Edit;
use App\Models\Empresa;
use App\Models\User;
use App\Support\Branding;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditTest extends TestCase
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

    public function test_un_admin_puede_acceder_a_la_pantalla_de_configuracion(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('configuracion.empresa'));

        $response->assertOk();
        $response->assertSeeLivewire(Edit::class);
    }

    public function test_un_trabajador_es_redirigido_por_no_tener_acceso_web(): void
    {
        $trabajador = $this->trabajador();

        $response = $this->actingAs($trabajador)->get(route('configuracion.empresa'));

        $response->assertRedirect('/login');
    }

    public function test_acceder_crea_la_fila_singleton_con_defaults(): void
    {
        $admin = $this->admin();

        $this->assertDatabaseCount('empresa', 0);

        Livewire::actingAs($admin)->test(Edit::class);

        $this->assertDatabaseCount('empresa', 1);
        $this->assertDatabaseHas('empresa', [
            'nombre' => 'ELECIND',
            'color_primario' => '#871f1f',
            'color_secundario' => '#f5e6e6',
        ]);
    }

    public function test_modelo_actual_devuelve_siempre_la_misma_fila(): void
    {
        $primera = Empresa::actual();
        $segunda = Empresa::actual();

        $this->assertSame($primera->id, $segunda->id);
        $this->assertSame(1, Empresa::count());
    }

    public function test_un_admin_puede_actualizar_datos_basicos(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nombre', 'Elecind Industrial')
            ->set('form.nombre_comercial', 'ELECIND')
            ->set('form.cif', 'B12345678')
            ->set('form.email_contacto', 'contacto@elecind.test')
            ->set('form.color_primario', '#1d4ed8')
            ->set('form.color_secundario', '#dbeafe')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('empresa', [
            'nombre' => 'Elecind Industrial',
            'cif' => 'B12345678',
            'color_primario' => '#1d4ed8',
            'color_secundario' => '#dbeafe',
        ]);
    }

    public function test_validacion_color_primario_debe_ser_hex_valido(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.color_primario', 'azul')
            ->call('guardar')
            ->assertHasErrors(['form.color_primario']);
    }

    public function test_validacion_nombre_obligatorio(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nombre', '')
            ->call('guardar')
            ->assertHasErrors(['form.nombre' => 'required']);
    }

    public function test_validacion_token_caducidad_dias_entre_1_y_90(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.token_caducidad_dias', 0)
            ->call('guardar')
            ->assertHasErrors(['form.token_caducidad_dias']);

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.token_caducidad_dias', 91)
            ->call('guardar')
            ->assertHasErrors(['form.token_caducidad_dias']);
    }

    public function test_subir_logo_lo_guarda_en_disco_publico_y_persiste_la_ruta(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $imagen = UploadedFile::fake()->image('logo.png', 200, 200);

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nuevoLogo', $imagen)
            ->call('guardar')
            ->assertHasNoErrors();

        $empresa = Empresa::actual();
        $this->assertNotNull($empresa->logo_path);
        $this->assertStringStartsWith('branding/', $empresa->logo_path);
        Storage::disk('public')->assertExists($empresa->logo_path);
    }

    public function test_subir_un_nuevo_logo_elimina_el_anterior(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $primero = UploadedFile::fake()->image('viejo.png', 100, 100);
        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nuevoLogo', $primero)
            ->call('guardar');

        $rutaVieja = Empresa::actual()->logo_path;
        $this->assertNotNull($rutaVieja);

        $segundo = UploadedFile::fake()->image('nuevo.png', 100, 100);
        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nuevoLogo', $segundo)
            ->call('guardar');

        $rutaNueva = Empresa::actual()->logo_path;
        $this->assertNotSame($rutaVieja, $rutaNueva);
        Storage::disk('public')->assertMissing($rutaVieja);
        Storage::disk('public')->assertExists($rutaNueva);
    }

    public function test_quitar_logo_marca_la_intencion_y_lo_borra_al_guardar(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $imagen = UploadedFile::fake()->image('logo.png', 100, 100);
        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nuevoLogo', $imagen)
            ->call('guardar');

        $rutaInicial = Empresa::actual()->logo_path;
        $this->assertNotNull($rutaInicial);

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->call('quitarLogo')
            ->assertSet('form.eliminarLogo', true)
            ->call('guardar');

        $this->assertNull(Empresa::actual()->logo_path);
        Storage::disk('public')->assertMissing($rutaInicial);
    }

    public function test_branding_helper_devuelve_el_logo_configurado(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $imagen = UploadedFile::fake()->image('marca.png', 100, 100);
        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.nombre_comercial', 'Marca Test')
            ->set('form.nuevoLogo', $imagen)
            ->call('guardar');

        Branding::limpiarCache();

        $this->assertNotNull(Branding::logoUrl());
        $this->assertSame('Marca Test', Branding::nombre());
    }

    public function test_branding_helper_devuelve_colores_configurados(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(Edit::class)
            ->set('form.color_primario', '#0ea5e9')
            ->set('form.color_secundario', '#e0f2fe')
            ->call('guardar');

        Branding::limpiarCache();

        $this->assertSame('#0ea5e9', Branding::colorPrimario());
        $this->assertSame('#e0f2fe', Branding::colorSecundario());
    }
}
