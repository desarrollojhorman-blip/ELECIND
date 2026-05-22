<?php

namespace App\Http\Controllers\Conceptos;

use App\Models\Concepto;
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
        $verPapelera = (bool) $request->query('papelera', false);
        $ordenColumna = (string) $request->query('orden', 'nombre');
        $ordenDireccion = (string) $request->query('dir', 'asc');

        $query = $verPapelera ? Concepto::onlyTrashed() : Concepto::query();

        if (! $verPapelera) {
            if ($filtroEstado === 'activos') {
                $query->where('activo', true);
            } elseif ($filtroEstado === 'inactivos') {
                $query->where('activo', false);
            }
        }

        if ($buscar !== '') {
            $termino = '%'.trim($buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('nombre', 'like', $termino)
                    ->orWhere('descripcion', 'like', $termino);
            });
        }

        $columnasPermitidas = ['id', 'nombre', 'activo', 'created_at'];
        $columna = \in_array($ordenColumna, $columnasPermitidas, true) ? $ordenColumna : 'nombre';
        $direccion = $ordenDireccion === 'desc' ? 'desc' : 'asc';

        $conceptos = $query->orderBy($columna, $direccion)->get();

        $empresa = Empresa::actual();
        $logoPath = $this->resolverLogoPath($empresa);

        $data = [
            'conceptos' => $conceptos,
            'orientacion' => $orientacion,
            'logoPath' => $logoPath,
            'empresaNombre' => Branding::nombre(),
            'colorPrimario' => Branding::colorPrimario(),
            'colorSecundario' => Branding::colorSecundario(),
            'colorTextoEncabezado' => Branding::colorTextoEncabezado(),
            'fecha' => now()->format('d/m/Y H:i'),
            'total' => $conceptos->count(),
            'filtrosActivos' => $this->describeFiltros($buscar, $filtroEstado, $verPapelera),
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

        $html = view('pdf.conceptos.lista', $data)->render();
        $mpdf->WriteHTML($html);

        $fecha = now()->format('Y-m-d');

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"conceptos_{$fecha}_{$orientacion}.pdf\"",
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

    private function describeFiltros(string $buscar, string $estado, bool $verPapelera): string
    {
        $partes = [];

        if ($buscar !== '') {
            $partes[] = "Búsqueda: «{$buscar}»";
        }

        if ($verPapelera) {
            $partes[] = 'Solo papelera';
        } elseif ($estado !== '') {
            $partes[] = 'Estado: '.ucfirst($estado);
        }

        return implode(' · ', $partes);
    }
}
