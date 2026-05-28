<?php

namespace App\Exports;

use App\Models\Ausencia;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AusenciasExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string  $buscar,
        private readonly ?int    $filtroTrabajador,
        private readonly string  $filtroTipo,
        private readonly string  $filtroEstado,
        private readonly string  $fechaDesde,
        private readonly string  $fechaHasta,
        private readonly bool    $verPapelera,
        private readonly string  $ordenColumna,
        private readonly string  $ordenDireccion,
    ) {}

    public function query(): Builder
    {
        $query = $this->verPapelera
            ? Ausencia::onlyTrashed()->with(['trabajador', 'aprobador'])
            : Ausencia::with(['trabajador', 'aprobador']);

        if (! $this->verPapelera) {
            $query
                ->when($this->filtroTrabajador, fn ($q) => $q->where('trabajador_id', $this->filtroTrabajador))
                ->when($this->filtroTipo,        fn ($q) => $q->where('tipo', $this->filtroTipo))
                ->when($this->filtroEstado,      fn ($q) => $q->where('estado', $this->filtroEstado))
                ->when($this->fechaDesde,        fn ($q) => $q->whereDate('fecha_inicio', '>=', $this->fechaDesde))
                ->when($this->fechaHasta,        fn ($q) => $q->whereDate('fecha_fin', '<=', $this->fechaHasta));
        }

        if ($this->buscar !== '') {
            $term = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('motivo', 'like', $term)
                  ->orWhere('observaciones', 'like', $term)
                  ->orWhereHas('trabajador', fn ($u) => $u->where('nombre', 'like', $term)
                      ->orWhere('apellidos', 'like', $term));
            });
        }

        $permitidas = ['id', 'fecha_inicio', 'fecha_fin', 'estado', 'tipo'];
        $columna    = \in_array($this->ordenColumna, $permitidas, true) ? $this->ordenColumna : 'id';
        $direccion  = $this->ordenDireccion === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($columna, $direccion);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'ID',
            'Trabajador',
            'Nº Empleado',
            'Tipo',
            'Fecha inicio',
            'Fecha fin',
            'Días',
            'Estado',
            'Aprobado por',
            'Fecha aprobación',
            'Motivo',
            'Observaciones',
        ];
    }

    /** @param Ausencia $row */
    public function map($row): array
    {
        return [
            $row->id,
            trim($row->trabajador?->apellidos . ' ' . $row->trabajador?->nombre),
            $row->trabajador?->numero_empleado ?? '',
            $row->tipo->etiqueta(),
            $row->fecha_inicio->format('d/m/Y'),
            $row->fecha_fin->format('d/m/Y'),
            $row->diasNaturales(),
            $row->estado->etiqueta(),
            $row->aprobador ? trim($row->aprobador->apellidos . ' ' . $row->aprobador->nombre) : '',
            $row->aprobado_at?->format('d/m/Y H:i') ?? '',
            $row->motivo ?? '',
            $row->observaciones ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
