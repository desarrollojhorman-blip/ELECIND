<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HorasExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private const DIAS = ['', 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    private const TIPO_HORA_ETIQUETAS = [
        'laboral'       => 'Laboral',
        'laboral_noche' => 'Lab. Noche',
        'festivo'       => 'Festivo',
        'festivo_noche' => 'Fest. Noche',
    ];

    public function __construct(
        private readonly ?int $filtroTrabajador,
        private readonly ?int $filtroCliente,
        private readonly ?int $filtroProyecto,
        private readonly string $filtroEstado,
        private readonly string $fechaDesde,
        private readonly string $fechaHasta,
    ) {}

    public function query(): Builder
    {
        $idsTrabajadores = User::role('trabajador')->withTrashed()->pluck('id');

        return DB::table('albaran_lineas_personal as alp')
            ->join('albaranes as a', 'a.id', '=', 'alp.albaran_id')
            ->leftJoin('users as u', 'u.id', '=', 'alp.trabajador_id')
            ->leftJoin('clientes as c', 'c.id', '=', 'a.cliente_id')
            ->leftJoin('proyectos as p', 'p.id', '=', 'a.proyecto_id')
            ->leftJoin('conceptos as con', 'con.id', '=', 'a.concepto_id')
            ->whereNull('a.deleted_at')
            ->whereIn('alp.trabajador_id', $idsTrabajadores)
            ->when($this->filtroTrabajador, fn ($q) => $q->where('alp.trabajador_id', $this->filtroTrabajador))
            ->when($this->filtroCliente, fn ($q) => $q->where('a.cliente_id', $this->filtroCliente))
            ->when($this->filtroProyecto, fn ($q) => $q->where('a.proyecto_id', $this->filtroProyecto))
            ->when($this->filtroEstado, fn ($q) => $q->where('a.estado', $this->filtroEstado))
            ->when($this->fechaDesde, fn ($q) => $q->whereDate('a.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn ($q) => $q->whereDate('a.fecha', '<=', $this->fechaHasta))
            ->select([
                'a.fecha',
                'a.numero as albaran_numero',
                'a.estado',
                'a.tipo_hora',
                'alp.horas',
                'alp.horas_extra',
                'u.numero_empleado',
                'u.tasa_hora',
                'u.tasa_extra',
                'u.tasa_festivo',
                DB::raw("COALESCE(u.nombre, alp.trabajador_nombre_snapshot, '') as trabajador_nombre"),
                DB::raw("COALESCE(u.apellidos, alp.trabajador_apellidos_snapshot, '') as trabajador_apellidos"),
                DB::raw("COALESCE(c.nombre, a.cliente_nombre_snapshot, a.cliente_texto, '') as cliente_nombre"),
                DB::raw("p.codigo as proyecto_codigo"),
                DB::raw("COALESCE(p.nombre, a.proyecto_nombre_snapshot, a.proyecto_texto, '') as proyecto_nombre"),
                DB::raw("COALESCE(con.nombre, a.concepto_nombre_snapshot, a.concepto_texto, '') as concepto_nombre"),
            ])
            ->orderBy('a.fecha')
            ->orderBy('trabajador_apellidos')
            ->orderBy('trabajador_nombre')
            ->orderBy('a.id');
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return [
            'Fecha',
            'Día',
            'Nº Empleado',
            'Trabajador',
            'Nº Albarán',
            'Estado albarán',
            'Cliente',
            'Cód. Proyecto',
            'Proyecto',
            'Concepto',
            'Tipo jornada',
            'Horas normales',
            'Horas extra',
            'Tasa €/h',
            'Tasa €/h extra',
            'Tasa €/h festivo',
            'Importe horas normales',
            'Importe horas extra',
            'Total línea',
        ];
    }

    /** @param object $row */
    public function map($row): array
    {
        $fecha       = $row->fecha ? Carbon::parse($row->fecha) : null;
        $horas       = (float) $row->horas;
        $horasExtra  = (float) $row->horas_extra;
        $tasaHora    = (float) ($row->tasa_hora ?? 0);
        $tasaExtra   = (float) ($row->tasa_extra ?? 0);
        $tasaFestivo = (float) ($row->tasa_festivo ?? 0);

        // Si la jornada es festiva, las horas normales van a tasa_festivo;
        // si es laboral, a tasa_hora. Las extras van siempre a tasa_extra.
        $esFestivo = str_starts_with((string) $row->tipo_hora, 'festivo');
        $tasaAplicableNormal = $esFestivo ? $tasaFestivo : $tasaHora;

        $importeNormales = round($horas * $tasaAplicableNormal, 2);
        $importeExtras   = round($horasExtra * $tasaExtra, 2);
        $totalLinea      = round($importeNormales + $importeExtras, 2);

        $trabajador = trim($row->trabajador_apellidos.' '.$row->trabajador_nombre);

        return [
            $fecha?->format('d/m/Y') ?? '',
            $fecha ? (self::DIAS[$fecha->dayOfWeek] ?? '') : '',
            $row->numero_empleado ?? '',
            $trabajador !== '' ? $trabajador : '—',
            $row->albaran_numero ?? '',
            ucfirst((string) $row->estado),
            $row->cliente_nombre ?: '—',
            $row->proyecto_codigo ?: '',
            $row->proyecto_nombre ?: '—',
            $row->concepto_nombre ?: '—',
            self::TIPO_HORA_ETIQUETAS[$row->tipo_hora] ?? $row->tipo_hora,
            $horas,
            $horasExtra,
            $tasaHora,
            $tasaExtra,
            $tasaFestivo,
            $importeNormales,
            $importeExtras,
            $totalLinea,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }

    public function title(): string
    {
        return 'Control de Horas';
    }
}
