<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export del Informe de horas: vuelca exactamente lo que se ve en pantalla
 * (mismo rango, misma agrupación, mismas columnas) a una hoja Excel.
 *
 * Si hay desglose, las sub-filas se emiten justo debajo de su fila principal,
 * con la etiqueta indentada y prefijada con "↳".
 */
class InformeHorasExport implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  Collection<int, array<string, mixed>>  $filas
     * @param  array<string, float|null>  $total
     */
    public function __construct(
        private Collection $filas,
        private array $total,
        private string $etiquetaColumna,
        private ?string $etiquetaDesglose = null,
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rows = [];

        foreach ($this->filas as $f) {
            $rows[] = $this->fila($f['etiqueta'], $f);

            foreach ($f['hijos'] ?? [] as $h) {
                $rows[] = $this->fila('   ↳ '.$h['etiqueta'], $h);
            }
        }

        $rows[] = $this->fila('TOTAL', $this->total);

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $n
     * @return array<int, mixed>
     */
    private function fila(string $etiqueta, array $n): array
    {
        return [
            $etiqueta,
            $this->num($n['horas']),
            $this->num($n['coste']),
            $this->num($n['facturacion']),
            $this->num($n['margen']),
            $this->num($n['precio_hora']),
            $this->num($n['coste_hora']),
            ($n['pct_margen'] ?? null) !== null ? round($n['pct_margen'] * 100, 1) : null,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $primera = $this->etiquetaDesglose !== null
            ? $this->etiquetaColumna.' / '.$this->etiquetaDesglose
            : $this->etiquetaColumna;

        return [
            $primera,
            'Horas',
            'A pagar €',
            'A cobrar €',
            'Margen €',
            'Precio/hora €',
            'Coste/hora €',
            '% Margen',
        ];
    }

    public function title(): string
    {
        return 'Informe de horas';
    }

    private function num(float $v): float
    {
        return round($v, 2);
    }
}
