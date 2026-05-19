<?php

namespace Tests\Feature\Clientes;

use App\Livewire\Clientes\Editar;
use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CodigoClienteTest extends TestCase
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

    public function test_al_crear_sugiere_el_numero_mas_grande_mas_uno(): void
    {
        Cliente::factory()->create(['codigo_cliente' => 3]);
        Cliente::factory()->create(['codigo_cliente' => 7]);
        Cliente::factory()->create(['codigo_cliente' => 10]);

        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->assertSet('form.codigo_cliente', '11');
    }

    public function test_el_codigo_debe_ser_numerico(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', 'abc')
            ->set('form.nombre', 'Cliente X')
            ->call('guardar')
            ->assertHasErrors(['form.codigo_cliente']);
    }

    public function test_avisa_si_el_codigo_ya_existe(): void
    {
        Cliente::factory()->create(['codigo_cliente' => 5, 'nombre' => 'Cliente Previo']);

        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', '5')
            ->set('form.nombre', 'Cliente Nuevo')
            ->call('guardar')
            ->assertHasErrors(['form.codigo_cliente']);

        $this->assertDatabaseMissing('clientes', ['nombre' => 'Cliente Nuevo']);
        $this->assertSame(1, Cliente::count());
    }

    public function test_el_codigo_es_inmutable_al_editar(): void
    {
        $cliente = Cliente::factory()->create(['codigo_cliente' => 5, 'nombre' => 'Cliente Editable']);

        Livewire::actingAs($this->admin())
            ->test(Editar::class, ['cliente' => $cliente])
            ->set('form.codigo_cliente', '999')
            ->set('form.nombre', 'Cliente Editable Cambiado')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'codigo_cliente' => 5,
            'nombre' => 'Cliente Editable Cambiado',
        ]);
        $this->assertDatabaseMissing('clientes', ['codigo_cliente' => 999]);
    }

    public function test_el_codigo_no_puede_superar_100000(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', '100001')
            ->set('form.nombre', 'Cliente Tope')
            ->call('guardar')
            ->assertHasErrors(['form.codigo_cliente']);
    }

    public function test_el_nombre_no_puede_superar_150_caracteres(): void
    {
        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', '50')
            ->set('form.nombre', str_repeat('a', 151))
            ->call('guardar')
            ->assertHasErrors(['form.nombre']);
    }

    public function test_el_cif_puede_repetirse(): void
    {
        Cliente::factory()->create(['codigo_cliente' => 10, 'cif' => 'B12345678']);

        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', '11')
            ->set('form.nombre', 'Cliente con CIF repetido')
            ->set('form.cif', 'B12345678')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame(2, Cliente::where('cif', 'B12345678')->count());
    }

    public function test_borrar_archiva_el_codigo_y_lo_libera(): void
    {
        $cliente = Cliente::factory()->create(['codigo_cliente' => 5]);
        $cliente->delete();

        $borrado = Cliente::withTrashed()->find($cliente->id);
        $this->assertNull($borrado->codigo_cliente);
        $this->assertSame(5, $borrado->codigo_cliente_anterior);

        // El código 5 queda libre para un cliente nuevo.
        Livewire::actingAs($this->admin())
            ->test(Editar::class)
            ->set('form.codigo_cliente', '5')
            ->set('form.nombre', 'Reusa el 5')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('clientes', [
            'nombre'         => 'Reusa el 5',
            'codigo_cliente' => 5,
            'deleted_at'     => null,
        ]);
    }

    public function test_restaurar_asigna_el_siguiente_codigo_libre(): void
    {
        $a = Cliente::factory()->create(['codigo_cliente' => 5, 'nombre' => 'Cliente A']);
        $a->delete();                                        // 5 liberado, archivado

        Cliente::factory()->create(['codigo_cliente' => 5]); // otro activo reutiliza el 5

        $a->restore();
        $a->refresh();

        $this->assertNotNull($a->codigo_cliente);
        $this->assertNotSame(5, $a->codigo_cliente); // no recupera el viejo
        $this->assertSame(6, $a->codigo_cliente);    // siguiente libre = max(5)+1
        $this->assertSame(5, $a->codigo_cliente_anterior); // se conserva histórico
    }
}
