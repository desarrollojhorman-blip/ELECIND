<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (auth()->user()->acceso === 'mobile' || auth()->user()->acceso === 'ambos')) {
            return $next($request);
        }

        return redirect('/login')->with('error', 'Acceso denegado. No tienes permisos para acceder al panel móvil.');
    }
}
