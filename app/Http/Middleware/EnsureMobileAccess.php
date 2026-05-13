<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user !== null && $user->tieneAccesoMovil()) {
            return $next($request);
        }

        return redirect('/login')->with('error', 'Acceso denegado. No tienes permisos para acceder al panel móvil.');
    }
}
