<?php

namespace Tests\Feature\Clientes;

use App\Livewire\Clientes\Importar;
use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ImportarTest extends TestCase
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

    /**
     * @param  array<int, array<int, string>>  $filas
     */
    private function csvFile(array $filas, string $nombre = 'clientes.csv'): UploadedFile
    {
        $contenido = implode("\n", array_map(
            fn (array $cols): string => implode(',', $cols),
            $filas
        ))."\n";

        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }

    public function test_importa_filas_validas_correctamente(): void
    {
        $archivo = $this->csvFile([
            ['codigo', 'nombre', 'cif'],
            ['100', 'Acme SA', 'B11111111'],
            ['101', 'Globex SL', 'B22222222'],
        ]);

        Livewire::actingAs($this->admin())
            ->test(Importar::class)
            ->set('archivo', $archivo)
            ->call('procesarArchivo')
            ->call('importar');

        $this->assertDatabaseHas('clientes', ['codigo_cliente' => 100, 'nombre' => 'Acme SA']);
        $this->assertDatabaseHas('clientes', ['codigo_cliente' => 101, 'nombre' => 'Globex SL']);
    }

    public function test_cif_repetido_dentro_del_archivo_no_bloquea(): void
    {
        $archivo = $this->csvFile([
            ['codigo', 'nombre', 'cif'],
            ['200', 'Cliente A', 'B99999999'],
            ['201', 'Cliente B', 'B99999999'], // mismo CIF: debe permitirse
        ]);

        Livewire::actingAs($this->admin())
            ->test(Importar::class)
            ->set('archivo', $archivo)
            ->call('procesarArchivo')
            ->call('importar');

        $this->assertSame(2, Cliente::where('cif', 'B99999999')->count());
    }

    public function test_cif_repetido_contra_bd_no_bloquea(): void
    {
        Cliente::factory()->create(['codigo_cliente' => 300, 'cif' => 'B77777777']);

        $archivo = $this->csvFile([
            ['codigo', 'nombre', 'cif'],
            ['301', 'Nuevo Cliente', 'B77777777'], // CIF ya en BD: debe permitirse
        ]);

        Livewire::actingAs($this->admin())
            ->test(Importar::class)
            ->set('archivo', $archivo)
            ->call('procesarArchivo')
            ->call('importar');

        $this->assertSame(2, Cliente::where('cif', 'B77777777')->count());
    }

    public function test_codigo_cliente_repetido_dentro_del_archivo_bloquea(): void
    {
        $archivo = $this->csvFile([
            ['codigo', 'nombre'],
            ['400', 'Cliente A'],
            ['400', 'Cliente B'], // código repetido en el archivo
        ]);

        $componente = Livewire::actingAs($this->admin())
            ->test(Importar::class)
            ->set('archivo', $archivo)
            ->call('procesarArchivo')
            ->call('importar');

        $this->assertNotEmpty($componente->get('errores'));
        // Regla all-or-nothing: ninguna fila se importa si hay errores.
        $this->assertSame(0, Cliente::where('codigo_cliente', 400)->count());
    }

    public function test_codigo_cliente_repetido_contra_bd_bloquea(): void
    {
        Cliente::factory()->create(['codigo_cliente' => 500, 'nombre' => 'Existente']);

        $archivo = $this->csvFile([
            ['codigo', 'nombre'],
            ['500', 'Nuevo Intento'], // código ya en BD
        ]);

        $componente = Livewire::actingAs($this->admin())
            ->test(Importar::class)
            ->set('archivo', $archivo)
            ->call('procesarArchivo')
            ->call('importar');

        $this->assertNotEmpty($componente->get('errores'));
        // Sigue solo el original; el "Nuevo Intento" no entró.
        $this->assertSame(1, Cliente::where('codigo_cliente', 500)->count());
        $this->assertDatabaseHas('clientes', ['codigo_cliente' => 500, 'nombre' => 'Existente']);
        $this->assertDatabaseMissing('clientes', ['nombre' => 'Nuevo Intento']);
    }
}
