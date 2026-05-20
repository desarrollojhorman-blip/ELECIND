<?php

namespace App\Http\Controllers\Usuarios;

use App\Exports\UsuariosExport;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarExcelController
{
    public function __invoke(Request $request): BinaryFileResponse
    {
        $buscar = (string) $request->query('q', '');
        $filtroEstado = (string) $request->query('estado', '');
        $filtroTipo = (string) $request->query('tipo', '');
        $filtroRol = (string) $request->query('rol', '');
        $filtroEmpresa = (string) $request->query('empresa', '');
        $ordenColumna = (string) $request->query('orden', 'nombre');
        $ordenDireccion = (string) $request->query('dir', 'asc');

        /** @var User|null $actor */
        $actor = auth()->user();
        $nivelActor = $actor?->nivelMaximo() ?? 0;

        $export = new UsuariosExport(
            buscar: $buscar,
            filtroEstado: $filtroEstado,
            filtroTipo: $filtroTipo,
            filtroRol: $filtroRol,
            filtroEmpresa: $filtroEmpresa,
            ordenColumna: $ordenColumna,
            ordenDireccion: $ordenDireccion,
            nivelActor: $nivelActor,
        );

        $fecha = now()->format('Y-m-d');

        return Excel::download($export, "usuarios_{$fecha}.xlsx");
    }
}
