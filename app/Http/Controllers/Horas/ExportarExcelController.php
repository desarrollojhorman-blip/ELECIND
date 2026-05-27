<?php

namespace App\Http\Controllers\Horas;

use App\Exports\HorasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarExcelController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $filtroTrabajador = $request->filled('trabajador') ? (int) $request->query('trabajador') : null;
        $filtroCliente    = $request->filled('cliente') ? (int) $request->query('cliente') : null;
        $filtroProyecto   = $request->filled('proyecto') ? (int) $request->query('proyecto') : null;
        $filtroEstado     = (string) $request->query('estado', '');
        $fechaDesde       = (string) $request->query('desde', '');
        $fechaHasta       = (string) $request->query('hasta', '');

        $export = new HorasExport(
            filtroTrabajador: $filtroTrabajador,
            filtroCliente: $filtroCliente,
            filtroProyecto: $filtroProyecto,
            filtroEstado: $filtroEstado,
            fechaDesde: $fechaDesde,
            fechaHasta: $fechaHasta,
        );

        $sufijo = match (true) {
            $fechaDesde !== '' && $fechaHasta !== '' => "{$fechaDesde}_a_{$fechaHasta}",
            $fechaDesde !== ''                       => "desde_{$fechaDesde}",
            $fechaHasta !== ''                       => "hasta_{$fechaHasta}",
            default                                  => now()->format('Y-m-d'),
        };

        return Excel::download($export, "control_horas_{$sufijo}.xlsx");
    }
}
