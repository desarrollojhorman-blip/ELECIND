<?php

use App\Http\Middleware\EnsureMobileAccess;
use App\Http\Middleware\EnsureModuloMateriales;
use App\Http\Middleware\EnsureWebAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        then: function (): void {
            Route::middleware('web')
                ->group(base_path('routes/mobile.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Producción va detrás de proxy/reverse-proxy; confiar en cabeceras
        // forwarded evita desajustes de esquema/host en URLs firmadas (Livewire uploads).
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );

        $middleware->alias([
            'ensure.web.access'      => EnsureWebAccess::class,
            'ensure.mobile.access'   => EnsureMobileAccess::class,
            'modulo.materiales'      => EnsureModuloMateriales::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
