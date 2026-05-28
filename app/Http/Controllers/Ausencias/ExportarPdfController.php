<?php

namespace App\Http\Controllers\Ausencias;

use App\Enums\EstadoAusencia;
use App\Enums\TipoAusencia;
use App\Models\Ausencia;
use App\Models\Empresa;
use App\Models\User;
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

        $buscar           = (string) $request->query('q', '');
        $filtroTrabajador = $request->query('trabajador') !== null ? (int) $request->query('trabajador') : null;
        $filtroTipo       = (string) $request->query('tipo', '');
        $filtroEstado     = (string) $request->query('estado', '');
        $fechaDesde       = (string) $request->query('desde', '');
        $fechaHasta       = (string) $request->query('hasta', '');
        $verPapelera      = (bool) $request->query('papelera', false);
        $ordenColumna     = (string) $request->query('orden', 'id');
        $ordenDireccion   = (string) $request->query('dir', 'desc');

        $query = $verPapelera
            ? Ausencia::onlyTrashed()->with(['trabajador', 'aprobador'])
            : Ausencia::with(['trabajador', 'aprobador']);

        if (! $verPapelera) {
            $query
                ->when($filtroTrabajador, fn ($q) => $q->where('trabajador_id', $filtroTrabajador))
                ->when($filtroTipo,       fn ($q) => $q->where('tipo', $filtroTipo))
                ->when($filtroEstado,     fn ($q) => $q->where('estado', $filtroEstado))
                ->when($fechaDesde,       fn ($q) => $q->whereDate('fecha_inicio', '>=', $fechaDesde))
                ->when($fechaHasta,       fn ($q) => $q->whereDate('fecha_fin', '<=', $fechaHasta));
        }

        if ($buscar !== '') {
            $term = '%'.trim($buscar).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('motivo', 'like', $term)
                  ->orWhere('observaciones', 'like', $term)
                  ->orWhereHas('trabajador', fn ($u) => $u->where('nombre', 'like', $term)
                      ->orWhere('apellidos', 'like', $term));
            });
        }

        $permitidas = ['id', 'fecha_inicio', 'fecha_fin', 'estado', 'tipo'];
        $columna    = \in_array($ordenColumna, $permitidas, true) ? $ordenColumna : 'id';
        $direccion  = $ordenDireccion === 'asc' ? 'asc' : 'desc';

        $ausencias = $query->orderBy($columna, $direccion)->get();

        $empresa  = Empresa::actual();
        $logoPath = $this->resolverLogoPath($empresa);

        $data = [
            'ausencias'            => $ausencias,
            'orientacion'          => $orientacion,
            'logoPath'             => $logoPath,
            'empresaNombre'        => Branding::nombre(),
            'colorPrimario'        => Branding::colorPrimario(),
            'colorSecundario'      => Branding::colorSecundario(),
            'colorTextoEncabezado' => Branding::colorTextoEncabezado(),
            'fecha'                => now()->format('d/m/Y H:i'),
            'total'                => $ausencias->count(),
            'filtrosActivos'       => $this->describeFiltros(
                $buscar, $filtroTrabajador, $filtroTipo, $filtroEstado, $fechaDesde, $fechaHasta, $verPapelera
            ),
        ];

        $mpdf = new Mpdf([
            'orientation'   => $orientacion === 'horizontal' ? 'L' : 'P',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'margin_header' => 0,
            'margin_footer' => 5,
        ]);

        $html = view('pdf.ausencias.lista', $data)->render();
        $mpdf->WriteHTML($html);

        $fecha = now()->format('Y-m-d');

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"ausencias_{$fecha}_{$orientacion}.pdf\"",
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

    private function describeFiltros(
        string $buscar,
        ?int   $filtroTrabajador,
        string $filtroTipo,
        string $filtroEstado,
        string $fechaDesde,
        string $fechaHasta,
        bool   $verPapelera,
    ): string {
        $partes = [];

        if ($buscar !== '') {
            $partes[] = "Búsqueda: «{$buscar}»";
        }

        if ($filtroTrabajador !== null) {
            $trabajador = User::find($filtroTrabajador);
            if ($trabajador) {
                $partes[] = 'Trabajador: '.trim($trabajador->apellidos.' '.$trabajador->nombre);
            }
        }

        if ($filtroTipo !== '') {
            $partes[] = 'Tipo: '.TipoAusencia::from($filtroTipo)->etiqueta();
        }

        if ($verPapelera) {
            $partes[] = 'Solo papelera';
        } elseif ($filtroEstado !== '') {
            $partes[] = 'Estado: '.EstadoAusencia::from($filtroEstado)->etiqueta();
        }

        if ($fechaDesde !== '') {
            $partes[] = 'Desde: '.\Illuminate\Support\Carbon::parse($fechaDesde)->format('d/m/Y');
        }

        if ($fechaHasta !== '') {
            $partes[] = 'Hasta: '.\Illuminate\Support\Carbon::parse($fechaHasta)->format('d/m/Y');
        }

        return implode(' · ', $partes);
    }
}
