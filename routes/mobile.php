<?php

use App\Livewire\Mobile\Albaranes\Crear as AlbaranesCrear;
use App\Livewire\Mobile\Albaranes\Firmar as AlbaranesFirmar;
use App\Livewire\Mobile\Albaranes\Index as AlbaranesIndex;
use App\Livewire\Mobile\Albaranes\Ver as AlbaranesVer;
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

        Route::view('/albaranes/personalizado', 'mobile.placeholder', [
            'titulo' => 'Parte personalizado',
            'icono' => 'heroicon-o-document-text',
            'descripcion' => 'Parte con campos libres cuando faltan datos en el sistema. Lo normaliza el admin desde web.',
            'roadmap' => 'Iter. 3.5 · Albarán personalizado',
        ])->name('albaranes.personalizado');

        Route::get('/albaranes/{albaran}/editar', AlbaranesCrear::class)
            ->middleware('can:albaranes.crear_movil')
            ->name('albaranes.editar');

        Route::get('/albaranes/{albaran}/firmar', AlbaranesFirmar::class)
            ->middleware('can:albaranes.crear_movil')
            ->name('albaranes.firmar');

        Route::get('/albaranes/{albaran}', AlbaranesVer::class)
            ->name('albaranes.ver');

        // ─── Faltas (Fase 4) ────────────────────────────────────────────────
        Route::view('/ausencias', 'mobile.placeholder', [
            'titulo' => 'Faltas de Asistencia',
            'icono' => 'heroicon-o-calendar-days',
            'descripcion' => 'Solicita ausencias y consulta el estado de las ya solicitadas.',
            'roadmap' => 'Fase 4 · Ausencias e incidencias',
        ])->name('ausencias.index');

        // ─── Resumen (Fase 5) ───────────────────────────────────────────────
        Route::view('/resumen', 'mobile.placeholder', [
            'titulo' => 'Resumen mensual',
            'icono' => 'heroicon-o-chart-bar',
            'descripcion' => 'Total de horas trabajadas, tipos de hora, ausencias y desglose por proyecto del mes.',
            'roadmap' => 'Fase 5 · Reportes y exportación',
        ])->name('resumen.index');

        // ─── Incidencias (Fase 4) ───────────────────────────────────────────
        Route::view('/incidencias/nueva', 'mobile.placeholder', [
            'titulo' => 'Nueva incidencia',
            'icono' => 'heroicon-o-exclamation-circle',
            'descripcion' => 'Reporta una incidencia. El sistema detecta el contexto (albarán, ausencia o general) según desde dónde se cree.',
            'roadmap' => 'Fase 4 · Ausencias e incidencias',
        ])->name('incidencias.nueva');
    });
