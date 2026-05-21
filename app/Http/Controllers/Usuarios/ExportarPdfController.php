<?php

namespace App\Http\Controllers\Usuarios;

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
        $puedeVerTarifas = $actor?->can('usuarios.gestionar_tarifas') ?? false;

        $query = User::query()->with(['roles:id,name', 'cliente:id,nombre']);

        // Jerarquía: oculta usuarios con algún rol de nivel mayor al actor.
        $query->whereDoesntHave('roles', function (Builder $q) use ($nivelActor): void {
            $q->where('nivel', '>', $nivelActor);
        });

        if ($filtroEstado === 'activos') {
            $query->where('activo', true);
        } elseif ($filtroEstado === 'inactivos') {
            $query->where('activo', false);
        }

        if ($filtroTipo !== '') {
            $query->where('tipo_usuario', $filtroTipo);
        }

        if ($filtroRol !== '') {
            $query->whereHas('roles', fn (Builder $q) => $q->where('name', $filtroRol));
        }

        if (trim($filtroEmpresa) !== '') {
            $termino = '%'.trim($filtroEmpresa).'%';
            $query->whereHas('cliente', fn (Builder $q) => $q->where('nombre', 'like', $termino));
        }

        if ($buscar !== '') {
            $termino = '%'.trim($buscar).'%';
            $query->where(function (Builder $q) use ($termino): void {
                $q->where('username', 'like', $termino)
                    ->orWhere('nombre', 'like', $termino)
                    ->orWhere('apellidos', 'like', $termino)
                    ->orWhere('email', 'like', $termino)
                    ->orWhere('dni', 'like', $termino);
            });
        }

        $columnasPermitidas = ['username', 'nombre', 'email', 'tipo_usuario', 'created_at'];
        $columna = \in_array($ordenColumna, $columnasPermitidas, true) ? $ordenColumna : 'nombre';
        $direccion = $ordenDireccion === 'desc' ? 'desc' : 'asc';

        $usuarios = $query->orderBy($columna, $direccion)->get();

        $empresa = Empresa::actual();
        $logoPath = $this->resolverLogoPath($empresa);

        $data = [
            'usuarios' => $usuarios,
            'orientacion' => $orientacion,
            'logoPath' => $logoPath,
            'empresaNombre' => Branding::nombre(),
            'colorPrimario' => Branding::colorPrimario(),
            'colorSecundario' => Branding::colorSecundario(),
            'colorTextoEncabezado' => Branding::colorTextoEncabezado(),
            'fecha' => now()->format('d/m/Y H:i'),
            'total' => $usuarios->count(),
            'filtrosActivos' => $this->describeFiltros($buscar, $filtroEstado, $filtroTipo, $filtroRol, $filtroEmpresa),
            'puedeVerTarifas' => $puedeVerTarifas,
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

        $html = view('pdf.usuarios.lista', $data)->render();
        $mpdf->WriteHTML($html);

        $fecha = now()->format('Y-m-d');

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"usuarios_{$fecha}_{$orientacion}.pdf\"",
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

    private function describeFiltros(string $buscar, string $estado, string $tipo, string $rol, string $empresa): string
    {
        $partes = [];

        if ($buscar !== '') {
            $partes[] = "Búsqueda: «{$buscar}»";
        }

        if ($estado !== '') {
            $partes[] = 'Estado: '.ucfirst($estado);
        }

        if ($tipo !== '') {
            $partes[] = 'Tipo: '.ucfirst($tipo);
        }

        if ($rol !== '') {
            $partes[] = "Rol: {$rol}";
        }

        if ($empresa !== '') {
            $partes[] = "Empresa: {$empresa}";
        }

        return implode(' · ', $partes);
    }
}
