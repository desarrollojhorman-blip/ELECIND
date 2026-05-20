<?php

namespace App\Http\Controllers\Clientes;

use App\Exports\ClientesExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarExcelController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $buscar = (string) $request->query('q', '');
        $filtroEstado = (string) $request->query('estado', '');
        $filtroProvincia = (string) $request->query('provincia', '');
        $ordenColumna = (string) $request->query('orden', 'nombre');
        $ordenDireccion = (string) $request->query('dir', 'asc');

        $export = new ClientesExport(
            buscar: $buscar,
            filtroEstado: $filtroEstado,
            filtroProvincia: $filtroProvincia,
            ordenColumna: $ordenColumna,
            ordenDireccion: $ordenDireccion,
        );

        $fecha = now()->format('Y-m-d');

        return Excel::download($export, "clientes_{$fecha}.xlsx");
    }
}
