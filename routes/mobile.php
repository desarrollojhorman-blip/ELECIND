<?php

use App\Livewire\Mobile\Albaranes\Crear as AlbaranesCrear;
use App\Livewire\Mobile\Albaranes\Firmar as AlbaranesFirmar;
use App\Livewire\Mobile\Albaranes\Index as AlbaranesIndex;
use App\Livewire\Mobile\Albaranes\Personalizado as AlbaranesPersonalizado;
use App\Livewire\Mobile\Albaranes\Ver as AlbaranesVer;
use App\Livewire\Mobile\Ausencias\Crear as AusenciasCrear;
use App\Livewire\Mobile\Ausencias\Index as AusenciasIndex;
use App\Livewire\Mobile\Horas\Index as HorasIndex;
use App\Livewire\Mobile\Incidencias\Crear as IncidenciasCrear;
use App\Livewire\Mobile\Incidencias\Index as IncidenciasIndex;
use App\Livewire\Mobile\Perfil\MiPerfil as PerfilMiPerfil;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ensure.mobile.access'])
    ->prefix('m')
    ->name('mobile.')
    ->group(function (): void {
        Route::view('/', 'mobile.dashboard')->name('dashboard');

        Route::get('/perfil', PerfilMiPerfil::class)->name('perfil');

        // ─── Albaranes (Iter. 3 — CRUD móvil) ───────────────────────────────
        Route::get('/albaranes', AlbaranesIndex::class)
            ->middleware('can:albaranes.ver_propios')
            ->name('albaranes.index');

        Route::get('/albaranes/nuevo', AlbaranesCrear::class)
            ->middleware('can:albaranes.crear_movil')
            ->name('albaranes.nuevo');

        Route::get('/albaranes/personalizado', AlbaranesPersonalizado::class)
            ->middleware('can:borradores.crear_movil')
            ->name('albaranes.personalizado');

        Route::get('/albaranes/{albaran}/editar', AlbaranesCrear::class)
            ->middleware('can:albaranes.crear_movil')
            ->name('albaranes.editar');

        Route::get('/albaranes/{albaran}/firmar', AlbaranesFirmar::class)
            ->middleware('can:albaranes.firmar')
            ->name('albaranes.firmar');

        Route::get('/albaranes/{albaran}', AlbaranesVer::class)
            ->name('albaranes.ver');

        // ─── Ausencias ──────────────────────────────────────────────────────
        Route::get('/ausencias', AusenciasIndex::class)
            ->middleware('can:ausencias.ver_propias')
            ->name('ausencias.index');

        Route::get('/ausencias/nueva', AusenciasCrear::class)
            ->middleware('can:ausencias.solicitar')
            ->name('ausencias.nueva');

        Route::get('/ausencias/{ausencia}/editar', AusenciasCrear::class)
            ->middleware('can:ausencias.solicitar')
            ->name('ausencias.editar');

        // ─── Resumen de horas ───────────────────────────────────────────────
        Route::get('/resumen', HorasIndex::class)->name('resumen.index');

        // ─── Incidencias ────────────────────────────────────────────────────
        Route::get('/incidencias', IncidenciasIndex::class)
            ->middleware('can:incidencias.ver_propias')
            ->name('incidencias.index');

        Route::get('/incidencias/nueva', IncidenciasCrear::class)
            ->middleware('can:incidencias.crear')
            ->name('incidencias.nueva');
    });
