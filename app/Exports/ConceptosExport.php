<?php

namespace App\Exports;

use App\Models\Concepto;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConceptosExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $buscar,
        private readonly string $filtroEstado,
        private readonly bool $verPapelera,
        private readonly string $ordenColumna,
        private readonly string $ordenDireccion,
    ) {}

    public function query(): Builder
    {
        $query = $this->verPapelera
            ? Concepto::onlyTrashed()
            : Concepto::query();

        if (! $this->verPapelera) {
            if ($this->filtroEstado === 'activos') {
                $query->where('activo', true);
            } elseif ($this->filtroEstado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $columnasPermitidas = ['id', 'nombre', 'activo', 'created_at'];
        $columna = \in_array($this->ordenColumna, $columnasPermitidas, true) ? $this->ordenColumna : 'nombre';
        $direccion = $this->ordenDireccion === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($columna, $direccion);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'ID',
            'Nombre',
            'Descripción',
            'Activo',
        ];
    }

    /** @param Concepto $row */
    public function map($row): array
    {
        return [
            $row->id,
            $row->nombre,
            $row->descripcion,
            $row->activo ? 'Sí' : 'No',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
