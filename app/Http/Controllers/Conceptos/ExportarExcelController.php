<?php

namespace App\Http\Controllers\Conceptos;

use App\Exports\ConceptosExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarExcelController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $buscar = (string) $request->query('q', '');
        $filtroEstado = (string) $request->query('estado', '');
        $verPapelera = (bool) $request->query('papelera', false);
        $ordenColumna = (string) $request->query('orden', 'nombre');
        $ordenDireccion = (string) $request->query('dir', 'asc');

        $export = new ConceptosExport(
            buscar: $buscar,
            filtroEstado: $filtroEstado,
            verPapelera: $verPapelera,
            ordenColumna: $ordenColumna,
            ordenDireccion: $ordenDireccion,
        );

        $fecha = now()->format('Y-m-d');

        return Excel::download($export, "conceptos_{$fecha}.xlsx");
    }
}
