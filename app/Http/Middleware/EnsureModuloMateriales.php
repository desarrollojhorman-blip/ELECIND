<?php

namespace App\Http\Middleware;

use App\Support\Modulos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuloMateriales
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Modulos::materialesAvanzado()) {
            abort(404);
        }

        return $next($request);
    }
}
