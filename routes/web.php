<?php

use App\Http\Controllers\LoginController;
use App\Livewire\Clientes\Index as ClientesIndex;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Empresa\Edit as EmpresaEdit;
use App\Livewire\Materiales\Familias\Index as FamiliasIndex;
use App\Livewire\Materiales\Index as MaterialesIndex;
use App\Livewire\Materiales\NumeroPedidos\Index as NumeroPedidosIndex;
use App\Livewire\Proyectos\Index as ProyectosIndex;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Usuarios\Index as UsuariosIndex;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'ensure.web.access'])->group(function (): void {
    Route::get('/', function () {
        return view('web.dashboard');
    })->name('web.dashboard');

    Route::get('/clientes', ClientesIndex::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.index');

    Route::get('/proyectos', ProyectosIndex::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.index');

    Route::get('/materiales/pedidos', NumeroPedidosIndex::class)
        ->middleware('can:pedidos.ver')
        ->name('materiales.pedidos');

    Route::get('/materiales/familias', FamiliasIndex::class)
        ->middleware('can:materiales.familias.ver')
        ->name('materiales.familias');

    Route::get('/materiales', MaterialesIndex::class)
        ->middleware('can:materiales.ver')
        ->name('materiales.index');

    Route::get('/usuarios', UsuariosIndex::class)
        ->middleware('can:usuarios.ver_todos')
        ->name('usuarios.index');

    Route::get('/conceptos', ConceptosIndex::class)
        ->middleware('can:conceptos.ver')
        ->name('conceptos.index');

    Route::get('/configuracion/empresa', EmpresaEdit::class)
        ->middleware('can:configuracion.empresa')
        ->name('configuracion.empresa');

    Route::get('/configuracion/roles', RolesIndex::class)
        ->middleware('can:roles.gestionar')
        ->name('configuracion.roles');
});
