<?php

namespace App\Http\Controllers\Api;

use App\Models\Proyecto;
use App\Support\Modulos;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProyectoOpcionesController
{
    public function __invoke(Proyecto $proyecto): JsonResponse
    {
        $user = Auth::user();
        $myId = (int) Auth::id();

        if (! $user->can('albaranes.ver_todos')) {
            $hasAccess = $proyecto->usuarios()->where('users.id', $myId)->exists()
                || $proyecto->responsable_principal_id === $myId;

            if (! $hasAccess) {
                abort(403);
            }
        }

        $conceptos = $proyecto->conceptos()
            ->orderBy('nombre')
            ->get(['conceptos.id', 'conceptos.nombre'])
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->nombre])
            ->values();

        $usuarios = $proyecto->usuarios()
            ->where('users.activo', true)
            ->orderBy('nombre')
            ->get(['users.id', 'users.nombre', 'users.apellidos']);

        $responsables = $usuarios
            ->map(fn ($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])
            ->values();

        $companeros = $usuarios
            ->filter(fn ($u) => $u->id !== $myId)
            ->map(fn ($u) => ['value' => $u->id, 'label' => trim($u->nombre.' '.$u->apellidos)])
            ->values();

        $materiales = Modulos::materialesAvanzado()
            ? $proyecto->materiales()
                ->orderBy('descripcion')
                ->get(['materiales.id', 'materiales.descripcion', 'materiales.unidad_medida', 'materiales.stock'])
                ->map(fn ($m) => [
                    'value' => $m->id,
                    'label' => $m->descripcion.' | '.rtrim(rtrim(number_format((float) $m->stock, 2, ',', ''), '0'), ',').' '.$m->unidad_medida,
                ])
                ->values()
            : collect();

        return response()->json([
            'concepto'    => $conceptos,
            'responsable' => $responsables,
            'companero'   => $companeros,
            'material'    => $materiales,
        ]);
    }
}
