<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsuariosExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $buscar,
        private readonly string $filtroEstado,
        private readonly string $filtroTipo,
        private readonly string $filtroRol,
        private readonly string $filtroEmpresa,
        private readonly string $ordenColumna,
        private readonly string $ordenDireccion,
        private readonly int $nivelActor,
        private readonly bool $puedeVerTarifas = false,
    ) {}

    public function query(): Builder
    {
        $query = User::query()->with(['roles:id,name', 'cliente:id,nombre']);

        // Jerarquía: oculta usuarios con algún rol de nivel mayor al actor.
        $nivel = $this->nivelActor;
        $query->whereDoesntHave('roles', function (Builder $q) use ($nivel): void {
            $q->where('nivel', '>', $nivel);
        });

        if ($this->filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($this->filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        if ($this->filtroTipo !== '') {
            $query->where('tipo_usuario', $this->filtroTipo);
        }

        if ($this->filtroRol !== '') {
            $rol = $this->filtroRol;
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $rol));
        }

        if (trim($this->filtroEmpresa) !== '') {
            $termino = '%'.trim($this->filtroEmpresa).'%';
            $query->whereHas('cliente', fn (Builder $q) => $q->where('nombre', 'like', $termino));
        }

        if ($this->buscar !== '') {
            $termino = '%'.trim($this->buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('username', 'like', $termino)
                    ->orWhere('nombre', 'like', $termino)
                    ->orWhere('apellidos', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('dni', 'like', $termino);
            });
        }

        $columnasPermitidas = ['username', 'nombre', 'email', 'tipo_usuario', 'created_at'];
        $columna = \in_array($this->ordenColumna, $columnasPermitidas, true) ? $this->ordenColumna : 'nombre';
        $direccion = $this->ordenDireccion === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($columna, $direccion);
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        $base = [
            'ID',
            'Usuario',
            'Nombre',
            'Apellidos',
            'Email',
            'DNI',
            'CIF',
            'Teléfono',
            'Nº empleado',
            'Tipo',
            'Rol',
            'Empresa',
            'Activo',
        ];

        if ($this->puedeVerTarifas) {
            $base[] = 'Tasa base (€/h)';
            $base[] = 'Tasa extra (€/h)';
            $base[] = 'Tasa festivo (€/h)';
        }

        return $base;
    }

    /** @param User $row */
    public function map($row): array
    {
        $base = [
            $row->id,
            $row->username,
            $row->nombre,
            $row->apellidos,
            $row->email,
            $row->dni,
            $row->cif,
            $row->telefono,
            $row->numero_empleado,
            ucfirst((string) $row->tipo_usuario),
            $row->roles->pluck('name')->join(', '),
            $row->cliente?->nombre,
            $row->activo ? 'Sí' : 'No',
        ];

        if ($this->puedeVerTarifas) {
            $base[] = $row->tasa_hora !== null ? (float) $row->tasa_hora : null;
            $base[] = $row->tasa_extra !== null ? (float) $row->tasa_extra : null;
            $base[] = $row->tasa_festivo !== null ? (float) $row->tasa_festivo : null;
        }

        return $base;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
