<?php

namespace App\Http\Controllers\Ausencias;

use App\Exports\AusenciasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarExcelController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $buscar           = (string) $request->query('q', '');
        $filtroTrabajador = $request->query('trabajador') !== null ? (int) $request->query('trabajador') : null;
        $filtroTipo       = (string) $request->query('tipo', '');
        $filtroEstado     = (string) $request->query('estado', '');
        $fechaDesde       = (string) $request->query('desde', '');
        $fechaHasta       = (string) $request->query('hasta', '');
        $verPapelera      = (bool) $request->query('papelera', false);
        $ordenColumna     = (string) $request->query('orden', 'id');
        $ordenDireccion   = (string) $request->query('dir', 'desc');

        $export = new AusenciasExport(
            buscar:           $buscar,
            filtroTrabajador: $filtroTrabajador,
            filtroTipo:       $filtroTipo,
            filtroEstado:     $filtroEstado,
            fechaDesde:       $fechaDesde,
            fechaHasta:       $fechaHasta,
            verPapelera:      $verPapelera,
            ordenColumna:     $ordenColumna,
            ordenDireccion:   $ordenDireccion,
        );

        $fecha = now()->format('Y-m-d');

        return Excel::download($export, "ausencias_{$fecha}.xlsx");
    }
}
