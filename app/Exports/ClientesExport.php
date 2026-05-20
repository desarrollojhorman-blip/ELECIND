<?php

namespace App\Exports;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $buscar,
        private readonly string $filtroEstado,
        private readonly string $filtroProvincia,
        private readonly string $ordenColumna,
        private readonly string $ordenDireccion,
    ) {}

    public function query(): Builder
    {
        $query = Cliente::query();

        if ($this->filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($this->filtroEstado === 'activas') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivas') {
            $query->where('activo', false);
        }

        if ($this->filtroProvincia !== '') {
            $query->where('provincia', 'like', '%'.trim($this->filtroProvincia).'%');
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('codigo_cliente', 'like', $termino)
                    ->orWhere('nombre', 'like', $termino)
                    ->orWhere('nombre_comercial', 'like', $termino)
                    ->orWhere('cif', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('telefono', 'like', $termino)
                    ->orWhere('poblacion', 'like', $termino);
            });
        }

        $columnasPermitidas = ['codigo_cliente', 'nombre', 'cif', 'poblacion', 'email', 'telefono', 'activo', 'created_at'];
        $columna = \in_array($this->ordenColumna, $columnasPermitidas, true) ? $this->ordenColumna : 'nombre';
        $direccion = $this->ordenDireccion === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($columna, $direccion);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Nombre comercial',
            'CIF',
            'Dirección',
            'C.P.',
            'Población',
            'Provincia',
            'Teléfono',
            'Email',
            'Activo',
            'Observaciones',
        ];
    }

    /** @param Cliente $row */
    public function map($row): array
    {
        return [
            $row->codigo_cliente,
            $row->nombre,
            $row->nombre_comercial,
            $row->cif,
            $row->direccion,
            $row->codigo_postal,
            $row->poblacion,
            $row->provincia,
            $row->telefono,
            $row->email,
            $row->activo ? 'Sí' : 'No',
            $row->observaciones,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
