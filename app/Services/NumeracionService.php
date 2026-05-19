<?php

namespace App\Services;

use App\Models\Albaran;
use App\Models\Borrador;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\NumeroPedido;
use App\Models\Proyecto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Genera números secuenciales para albaranes según la plantilla configurada
 * en `empresa.plantilla_numeracion_albaran`.
 *
 * Variables soportadas:
 *   {YYYY} → año en 4 dígitos
 *   {YY}   → año en 2 dígitos
 *   {MM}   → mes en 2 dígitos
 *   {NNNN} → secuencial dentro del año, 4 dígitos con ceros a la izquierda
 *   {NNN}  → secuencial dentro del año, 3 dígitos
 *   {NN}   → secuencial dentro del año, 2 dígitos
 *   {N}    → secuencial sin ceros a la izquierda
 */
class NumeracionService
{
    public const PLANTILLA_DEFECTO = 'ALB-{YYYY}-{NNNN}';

    public const PLANTILLA_DEFECTO_PEDIDO = 'PED-{YYYY}-{NNNN}';

    public const PLANTILLA_DEFECTO_PROYECTO = 'PROY-{NNNN}';

    public const PLANTILLA_DEFECTO_BORRADOR = 'BOR-{NNNN}';

    public function siguienteNumeroAlbaran(?Carbon $fecha = null): string
    {
        $fecha = $fecha ?? Carbon::now();
        $plantilla = $this->plantilla();

        return DB::transaction(function () use ($fecha, $plantilla): string {
            $secuencial = $this->siguienteSecuencial($fecha);

            return $this->aplicarPlantilla($plantilla, $fecha, $secuencial);
        });
    }

    /**
     * Devuelve la plantilla configurada para la empresa actual o el default.
     */
    public function plantilla(): string
    {
        $empresa = Empresa::query()->first();
        $plantilla = $empresa?->plantilla_numeracion_albaran;

        return ($plantilla !== null && $plantilla !== '')
            ? $plantilla
            : self::PLANTILLA_DEFECTO;
    }

    /**
     * Cuenta los albaranes ya existentes en el año de $fecha y suma 1.
     * Incluye soft-deleted para no reusar números.
     */
    private function siguienteSecuencial(Carbon $fecha): int
    {
        $existentes = Albaran::query()
            ->withTrashed()
            ->whereYear('fecha', $fecha->year)
            ->lockForUpdate()
            ->count();

        return $existentes + 1;
    }

    /**
     * Siguiente código de cliente = el número más grande existente + 1.
     * Cuenta el máximo (incl. papelera) sobre la columna entera, no las filas.
     */
    public function siguienteNumeroCliente(): int
    {
        return DB::transaction(function (): int {
            $max = Cliente::query()
                ->withTrashed()
                ->lockForUpdate()
                ->max('codigo_cliente');

            return ((int) $max) + 1;
        });
    }

    private function aplicarPlantillaCliente(string $plantilla, int $secuencial): string
    {
        $reemplazos = [
            '{NNNN}' => str_pad((string) $secuencial, 4, '0', STR_PAD_LEFT),
            '{NNN}'  => str_pad((string) $secuencial, 3, '0', STR_PAD_LEFT),
            '{NN}'   => str_pad((string) $secuencial, 2, '0', STR_PAD_LEFT),
            '{N}'    => (string) $secuencial,
        ];

return str_replace(array_keys($reemplazos), array_values($reemplazos), $plantilla);
    }

    // ── Nº Pedido ────────────────────────────────────────────────────────────

    public function siguienteNumeroPedido(?Carbon $fecha = null): string
    {
        $fecha = $fecha ?? Carbon::now();
        $plantilla = $this->plantillaPedido();

        return DB::transaction(function () use ($fecha, $plantilla): string {
            $secuencial = NumeroPedido::query()
                ->withTrashed()
                ->whereYear('fecha', $fecha->year)
                ->lockForUpdate()
                ->count() + 1;

            return $this->aplicarPlantilla($plantilla, $fecha, $secuencial);
        });
    }

    public function plantillaPedido(): string
    {
        $empresa = Empresa::query()->first();
        $plantilla = $empresa?->plantilla_numeracion_pedido;

        return ($plantilla !== null && $plantilla !== '')
            ? $plantilla
            : self::PLANTILLA_DEFECTO_PEDIDO;
    }

    // ── Código Proyecto ───────────────────────────────────────────────────────

    public function siguienteNumeroProyecto(): string
    {
        $plantilla = $this->plantillaProyecto();

        return DB::transaction(function () use ($plantilla): string {
            $secuencial = Proyecto::query()
                ->withTrashed()
                ->lockForUpdate()
                ->count() + 1;

            return $this->aplicarPlantillaCliente($plantilla, $secuencial);
        });
    }

    public function plantillaProyecto(): string
    {
        $empresa = Empresa::query()->first();
        $plantilla = $empresa?->plantilla_numeracion_proyecto;

        return ($plantilla !== null && $plantilla !== '')
            ? $plantilla
            : self::PLANTILLA_DEFECTO_PROYECTO;
    }

    // ── Nº Borrador ──────────────────────────────────────────────────────────

    public function siguienteNumeroBorrador(): string
    {
        $plantilla = $this->plantillaBorrador();

        return DB::transaction(function () use ($plantilla): string {
            $secuencial = Borrador::query()
                ->withTrashed()
                ->lockForUpdate()
                ->count() + 1;

            return $this->aplicarPlantillaCliente($plantilla, $secuencial);
        });
    }

    public function plantillaBorrador(): string
    {
        $empresa = Empresa::query()->first();
        $plantilla = $empresa?->plantilla_numeracion_borrador ?? null;

        return ($plantilla !== null && $plantilla !== '')
            ? $plantilla
            : self::PLANTILLA_DEFECTO_BORRADOR;
    }

    // ────────────────────────────────────────────────────────────────────────

    public function aplicarPlantilla(string $plantilla, Carbon $fecha, int $secuencial): string
    {
        $reemplazos = [
            '{YYYY}' => $fecha->format('Y'),
            '{YY}' => $fecha->format('y'),
            '{MM}' => $fecha->format('m'),
            '{NNNN}' => str_pad((string) $secuencial, 4, '0', STR_PAD_LEFT),
            '{NNN}' => str_pad((string) $secuencial, 3, '0', STR_PAD_LEFT),
            '{NN}' => str_pad((string) $secuencial, 2, '0', STR_PAD_LEFT),
            '{N}' => (string) $secuencial,
        ];

        return str_replace(array_keys($reemplazos), array_values($reemplazos), $plantilla);
    }
}
