<?php

namespace Tests\Feature\Albaranes;

use App\Models\Albaran;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Proyecto;
use App\Models\User;
use App\Services\NumeracionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NumeracionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_genera_numero_con_plantilla_por_defecto_si_no_hay_empresa(): void
    {
        $servicio = new NumeracionService;

        $numero = $servicio->siguienteNumeroAlbaran(Carbon::create(2026, 5, 14));

        $this->assertSame('ALB-2026-0001', $numero);
    }

    public function test_resuelve_variables_yyyy_yy_mm_y_nnnn(): void
    {
        $servicio = new NumeracionService;
        $fecha = Carbon::create(2026, 7, 3);

        $resultado = $servicio->aplicarPlantilla('AL-{YY}{MM}-{NNNN}', $fecha, 42);

        $this->assertSame('AL-2607-0042', $resultado);
    }

    public function test_usa_plantilla_de_empresa_si_existe(): void
    {
        Empresa::create([
            'nombre' => 'Elecind Test',
            'plantilla_numeracion_albaran' => 'PT-{YYYY}/{MM}/{NNN}',
            'color_primario' => '#871f1f',
            'color_secundario' => '#f5e6e6',
            'token_caducidad_dias' => 7,
        ]);

        $servicio = new NumeracionService;
        $numero = $servicio->siguienteNumeroAlbaran(Carbon::create(2026, 3, 10));

        $this->assertSame('PT-2026/03/001', $numero);
    }

    public function test_secuencial_se_incrementa_por_albaranes_existentes_del_anio(): void
    {
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create(['cliente_id' => $cliente->id]);
        $user = User::factory()->trabajador()->create();

        // 3 albaranes en 2026
        for ($i = 0; $i < 3; $i++) {
            Albaran::factory()->create([
                'cliente_id' => $cliente->id,
                'proyecto_id' => $proyecto->id,
                'creado_por' => $user->id,
                'fecha' => Carbon::create(2026, 2, 1),
            ]);
        }

        $servicio = new NumeracionService;
        $numero = $servicio->siguienteNumeroAlbaran(Carbon::create(2026, 5, 1));

        $this->assertSame('ALB-2026-0004', $numero);
    }

    public function test_secuencial_es_independiente_por_anio(): void
    {
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create(['cliente_id' => $cliente->id]);
        $user = User::factory()->trabajador()->create();

        // 5 albaranes en 2025
        for ($i = 0; $i < 5; $i++) {
            Albaran::factory()->create([
                'cliente_id' => $cliente->id,
                'proyecto_id' => $proyecto->id,
                'creado_por' => $user->id,
                'fecha' => Carbon::create(2025, 6, 1),
            ]);
        }

        $servicio = new NumeracionService;
        $numero = $servicio->siguienteNumeroAlbaran(Carbon::create(2026, 1, 15));

        // En 2026 no hay todavía → empieza desde 1
        $this->assertSame('ALB-2026-0001', $numero);
    }

    public function test_albaranes_soft_deleted_cuentan_para_la_secuencia(): void
    {
        $cliente = Cliente::factory()->create();
        $proyecto = Proyecto::factory()->create(['cliente_id' => $cliente->id]);
        $user = User::factory()->trabajador()->create();

        $a = Albaran::factory()->create([
            'cliente_id' => $cliente->id,
            'proyecto_id' => $proyecto->id,
            'creado_por' => $user->id,
            'fecha' => Carbon::create(2026, 2, 1),
        ]);
        $a->delete();

        $servicio = new NumeracionService;
        $numero = $servicio->siguienteNumeroAlbaran(Carbon::create(2026, 5, 1));

        $this->assertSame('ALB-2026-0002', $numero);
    }
}
