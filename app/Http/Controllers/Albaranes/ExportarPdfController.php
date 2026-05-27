<?php

namespace App\Http\Controllers\Albaranes;

use App\Models\Albaran;
use App\Models\Empresa;
use App\Support\Branding;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class ExportarPdfController
{
    public function __invoke(Request $request, Albaran $albaran): Response
    {
        Gate::authorize('albaranes.ver_todos');

        $albaran->load([
            'cliente',
            'proyecto',
            'concepto',
            'lineasPersonal.trabajador',
            'lineasMaterial.material',
            'firmas',
        ]);

        $empresa   = Empresa::actual();
        $logoPath  = $this->resolverLogoPath($empresa);
        $color     = Branding::colorPrimario();

        $conMateriales = $request->boolean('materiales', true);

        $html = view('pdf.albaran', compact('albaran', 'empresa', 'logoPath', 'color', 'conMateriales'))->render();

        $mpdf = new Mpdf([
            'margin_left'   => 14,
            'margin_right'  => 14,
            'margin_top'    => 14,
            'margin_bottom' => 14,
        ]);

        $mpdf->WriteHTML($html);

        $filename = 'albaran-' . $albaran->numero . '.pdf';

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
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
}
