<?php

namespace App\Livewire\Partes;

use App\Exports\InformeHorasExport;
use App\Models\AtributoHora;
use App\Models\Parte;
use App\Models\ParteLineaPersonal;
use App\Models\TarifaCliente;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Informe de horas: cuánto se paga al trabajador y cuánto se cobra al cliente
 * en un rango de fechas. Réplica de las hojas «informe» del Excel del cliente.
 *
 * Dos niveles de agrupación:
 *   - "Ver por"      (agrupacion): fila principal (trabajador / cliente / proyecto).
 *   - "Desglosar por" (desglose):  sub-filas expandibles dentro de cada principal.
 *     Ej.: Ver por Proyecto + Desglosar por Trabajador → dentro de cada proyecto,
 *     cuánto generó cada trabajador (para saber qué cobrar al cliente).
 *
 * ⚠️ CÁLCULO PROVISIONAL (se cambiará): de momento solo se contemplan los
 * campos que se multiplican por horas (sin pluses):
 *   - A pagar (coste)    = horas × tasa_hora + horas_extra × tasa_extra
 *   - A cobrar (factur.) = (horas + horas_extra) × tarifa_labor del proyecto
 * Toda la fórmula vive en {@see self::calcularLinea()} para cambiarla en un solo sitio.
 */
#[Layout('components.layouts.web', ['active' => 'partes_informe'])]
#[Title('Informe de horas')]
class Informe extends Component
{
    public const DIMENSIONES = ['trabajador', 'cliente', 'proyecto'];

    #[Url(as: 'agrupar')]
    public string $agrupacion = 'proyecto';

    #[Url(as: 'desglose')]
    public string $desglose = 'trabajador';

    #[Url(as: 'desde')]
    public string $fechaDesde = '';

    #[Url(as: 'hasta')]
    public string $fechaHasta = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Parte::class);

        if ($this->fechaDesde === '') {
            $this->fechaDesde = Carbon::now()->startOfYear()->toDateString();
        }
        if ($this->fechaHasta === '') {
            $this->fechaHasta = Carbon::now()->endOfYear()->toDateString();
        }
    }

    public function setAgrupacion(string $valor): void
    {
        if (! in_array($valor, self::DIMENSIONES, true)) {
            return;
        }
        $this->agrupacion = $valor;
        // Evita que principal y desglose coincidan.
        if ($this->desglose === $valor) {
            $this->desglose = '';
        }
    }

    public function setDesglose(string $valor): void
    {
        if ($valor !== '' && (! in_array($valor, self::DIMENSIONES, true) || $valor === $this->agrupacion)) {
            return;
        }
        $this->desglose = $valor;
    }

    /** Atajo: rango = mes en curso. */
    public function rangoMesActual(): void
    {
        $this->fechaDesde = Carbon::now()->startOfMonth()->toDateString();
        $this->fechaHasta = Carbon::now()->endOfMonth()->toDateString();
    }

    /** Atajo: rango = año en curso. */
    public function rangoAnioActual(): void
    {
        $this->fechaDesde = Carbon::now()->startOfYear()->toDateString();
        $this->fechaHasta = Carbon::now()->endOfYear()->toDateString();
    }

    /**
     * Cálculo provisional por línea. Devuelve [horas, coste, facturacion].
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private function calcularLinea(ParteLineaPersonal $l, float $tarifaLabor): array
    {
        $horasNorm = (float) $l->horas;
        $horasExtra = (float) $l->horas_extra;
        $horas = $horasNorm + $horasExtra;

        $coste = $horasNorm * (float) $l->trabajador_tasa_hora_snapshot
            + $horasExtra * (float) $l->trabajador_tasa_extra_snapshot;

        $facturacion = $horas * $tarifaLabor;

        return [$horas, $coste, $facturacion];
    }

    /**
     * Clave + etiqueta de una dimensión concreta para una línea.
     *
     * @return array{0: string, 1: string}
     */
    private function dimension(string $dim, ParteLineaPersonal $l, Parte $parte): array
    {
        return match ($dim) {
            'cliente' => [
                'cli-'.($parte->cliente_id ?? '0'),
                $parte->cliente_nombre_snapshot ?: '(sin cliente)',
            ],
            'proyecto' => [
                'pro-'.($parte->proyecto_id ?? '0'),
                trim(($parte->proyecto_codigo_snapshot ?? '').' '.($parte->proyecto_nombre_snapshot ?? '')) ?: '(sin proyecto)',
            ],
            'trabajador' => [
                'tra-'.($l->trabajador_id ?? '0'),
                trim(($l->trabajador_apellidos_snapshot ?? '').' '.($l->trabajador_nombre_snapshot ?? '')) ?: '(sin trabajador)',
            ],
            default => ['', ''],
        };
    }

    /**
     * Añade horas/coste/facturación a un nodo del árbol de agregación.
     *
     * @param  array<string, mixed>  $nodo
     */
    private function acumular(array &$nodo, string $etiqueta, float $horas, float $coste, float $facturacion): void
    {
        if (! isset($nodo['etiqueta'])) {
            $nodo['etiqueta'] = $etiqueta;
            $nodo['horas'] = 0.0;
            $nodo['coste'] = 0.0;
            $nodo['facturacion'] = 0.0;
        }
        $nodo['horas'] += $horas;
        $nodo['coste'] += $coste;
        $nodo['facturacion'] += $facturacion;
    }

    /** Calcula columnas derivadas (margen, €/h, %). */
    private function derivar(array $n): array
    {
        $n['margen'] = $n['facturacion'] - $n['coste'];
        $n['precio_hora'] = $n['horas'] > 0 ? $n['facturacion'] / $n['horas'] : 0.0;
        $n['coste_hora'] = $n['horas'] > 0 ? $n['coste'] / $n['horas'] : 0.0;
        $n['pct_margen'] = $n['facturacion'] > 0 ? $n['margen'] / $n['facturacion'] : null;

        return $n;
    }

    /**
     * Construye el árbol agregado (principal → desglose) + total general.
     *
     * @return array{filas: Collection<int, array<string, mixed>>, total: array<string, mixed>}
     */
    private function construirInforme(): array
    {
        $idLabor = AtributoHora::query()
            ->where('codigo', AtributoHora::COD_LABOR)
            ->value('id');

        $mapaTarifa = [];
        if ($idLabor !== null) {
            foreach (TarifaCliente::query()->where('atributo_id', $idLabor)->get(['cliente_id', 'tipo_proyecto_id', 'importe']) as $t) {
                $mapaTarifa[$t->cliente_id.'-'.$t->tipo_proyecto_id] = (float) $t->importe;
            }
        }

        $lineas = ParteLineaPersonal::query()
            ->whereHas('parte', fn ($q) => $q->whereBetween('fecha', [$this->fechaDesde, $this->fechaHasta]))
            ->with([
                'parte:id,cliente_id,cliente_nombre_snapshot,proyecto_id,proyecto_codigo_snapshot,proyecto_nombre_snapshot',
                'parte.proyecto:id,tipo_proyecto_id',
            ])
            ->get();

        $tieneDesglose = $this->desglose !== '' && $this->desglose !== $this->agrupacion;

        /** @var array<string, array<string, mixed>> $grupos */
        $grupos = [];
        $total = [];

        foreach ($lineas as $l) {
            $parte = $l->parte;
            if ($parte === null) {
                continue;
            }

            $tipoProyectoId = $parte->proyecto?->tipo_proyecto_id;
            $tarifaLabor = $mapaTarifa[$parte->cliente_id.'-'.$tipoProyectoId] ?? 0.0;
            [$horas, $coste, $facturacion] = $this->calcularLinea($l, $tarifaLabor);

            [$pk, $pl] = $this->dimension($this->agrupacion, $l, $parte);
            $grupos[$pk] ??= ['hijos' => []];
            $this->acumular($grupos[$pk], $pl, $horas, $coste, $facturacion);

            if ($tieneDesglose) {
                [$sk, $sl] = $this->dimension($this->desglose, $l, $parte);
                $grupos[$pk]['hijos'][$sk] ??= [];
                $this->acumular($grupos[$pk]['hijos'][$sk], $sl, $horas, $coste, $facturacion);
            }

            $this->acumular($total, 'TOTAL', $horas, $coste, $facturacion);
        }

        $filas = collect($grupos)
            ->map(function (array $g): array {
                $hijos = collect($g['hijos'])
                    ->map(fn (array $h) => $this->derivar($h))
                    ->sortByDesc('facturacion')
                    ->values();

                unset($g['hijos']);
                $g = $this->derivar($g);
                $g['hijos'] = $hijos;

                return $g;
            })
            ->sortByDesc('facturacion')
            ->values();

        $total = $total === [] ? $this->derivar(['etiqueta' => 'TOTAL', 'horas' => 0.0, 'coste' => 0.0, 'facturacion' => 0.0]) : $this->derivar($total);

        return ['filas' => $filas, 'total' => $total];
    }

    public function exportar(): BinaryFileResponse
    {
        Gate::authorize('viewAny', Parte::class);

        $datos = $this->construirInforme();

        $nombre = 'informe-horas_'.$this->agrupacion
            .($this->desglose !== '' && $this->desglose !== $this->agrupacion ? '-'.$this->desglose : '')
            .'_'.$this->fechaDesde.'_'.$this->fechaHasta.'.xlsx';

        return Excel::download(
            new InformeHorasExport(
                $datos['filas'],
                $datos['total'],
                ucfirst($this->agrupacion),
                $this->desglose !== '' && $this->desglose !== $this->agrupacion ? ucfirst($this->desglose) : null,
            ),
            $nombre
        );
    }

    public function render(): View
    {
        $datos = $this->construirInforme();

        return view('livewire.partes.informe', [
            'filas' => $datos['filas'],
            'total' => $datos['total'],
            'tieneDesglose' => $this->desglose !== '' && $this->desglose !== $this->agrupacion,
        ]);
    }
}
