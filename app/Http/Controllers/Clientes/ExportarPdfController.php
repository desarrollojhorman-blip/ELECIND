<?php

namespace App\Http\Controllers\Clientes;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Support\Branding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class ExportarPdfController
{
    public function __invoke(Request $request, string $orientacion): Response
    {
        abort_unless(\in_array($orientacion, ['vertical', 'horizontal'], true), 404);

        $buscar = (string) $request->query('q', '');
        $filtroEstado = (string) $request->query('estado', '');
        $filtroProvincia = (string) $request->query('provincia', '');
        $ordenColumna = (string) $request->query('orden', 'nombre');
        $ordenDireccion = (string) $request->query('dir', 'asc');

        $query = Cliente::query();

        if ($filtroEstado === 'papelera') {
            $query->onlyTrashed();
        } elseif ($filtroEstado === 'activas') {
            $query->where('activo', true);
        } elseif ($filtroEstado === 'inactivas') {
            $query->where('activo', false);
        }

        if ($filtroProvincia !== '') {
            $query->where('provincia', 'like', '%'.trim($filtroProvincia).'%');
        }

        if ($buscar !== '') {
            $termino = '%'.trim($buscar).'%';
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
        $columna = \in_array($ordenColumna, $columnasPermitidas, true) ? $ordenColumna : 'nombre';
        $direccion = $ordenDireccion === 'desc' ? 'desc' : 'asc';

        $clientes = $query->orderBy($columna, $direccion)->get();

        $empresa = Empresa::actual();
        $logoPath = $this->resolverLogoPath($empresa);

        $data = [
            'clientes' => $clientes,
            'orientacion' => $orientacion,
            'logoPath' => $logoPath,
            'empresaNombre' => Branding::nombre(),
            'colorPrimario' => Branding::colorPrimario(),
            'colorSecundario' => Branding::colorSecundario(),
            'colorTextoEncabezado' => Branding::colorTextoEncabezado(),
            'fecha' => now()->format('d/m/Y H:i'),
            'total' => $clientes->count(),
            'filtrosActivos' => $this->describeFiltros($buscar, $filtroEstado, $filtroProvincia),
        ];

        $mpdf = new Mpdf([
            'orientation' => $orientacion === 'horizontal' ? 'L' : 'P',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);

        $html = view('pdf.clientes.lista', $data)->render();
        $mpdf->WriteHTML($html);

        $fecha = now()->format('Y-m-d');

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"clientes_{$fecha}_{$orientacion}.pdf\"",
        ]);
    }

    private function resolverLogoPath(Empresa $empresa): ?string
    {
        $relativo = $empresa->logo_albaran_path ?: $empresa->logo_path;

        if ($relativo === null || $relativo === '') {
            return null;
        }

        $absoluto = Storage::disk('public')->path($relativo);

        return file_exists($absoluto) ? $absoluto : null;
    }

    private function describeFiltros(string $buscar, string $estado, string $provincia): string
    {
        $partes = [];

        if ($buscar !== '') {
            $partes[] = "Búsqueda: «{$buscar}»";
        }

        if ($estado !== '') {
            $partes[] = 'Estado: '.ucfirst($estado);
        }

        if ($provincia !== '') {
            $partes[] = "Provincia: {$provincia}";
        }

        return implode(' · ', $partes);
    }
}
