<?php

use App\Http\Controllers\Api\ProyectoOpcionesController;
use App\Http\Controllers\LoginController;
use App\Livewire\Clientes\Editar as ClientesEditar;
use App\Livewire\Clientes\Index as ClientesIndex;
use App\Livewire\Clientes\Ver as ClientesVer;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Configuracion\Ajustes as ConfiguracionAjustes;
use App\Livewire\Empresa\Edit as EmpresaEdit;
use App\Livewire\Materiales\Familias\Index as FamiliasIndex;
use App\Livewire\Materiales\Index as MaterialesIndex;
use App\Livewire\Materiales\NumeroPedidos\Index as NumeroPedidosIndex;
use App\Livewire\Perfil\MiPerfil;
use App\Livewire\Albaranes\Editar as AlbaranesEditar;
use App\Livewire\Albaranes\Index as AlbaranesIndex;
use App\Livewire\Albaranes\Ver as AlbaranesVer;
use App\Livewire\Proyectos\Editar as ProyectosEditar;
use App\Livewire\Proyectos\Grupos\Index as GruposProyectosIndex;
use App\Livewire\Proyectos\Index as ProyectosIndex;
use App\Livewire\Proyectos\Ver as ProyectosVer;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Usuarios\Index as UsuariosIndex;
use Illuminate\Support\Facades\Route;

// API ligera — accesible desde móvil y web (solo necesita auth)
Route::middleware('auth')->group(function (): void {
    Route::get('/api/proyecto/{proyecto}/opciones', ProyectoOpcionesController::class)
        ->name('api.proyecto.opciones');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'ensure.web.access'])->group(function (): void {
    Route::get('/', function () {
        return view('web.dashboard');
    })->name('web.dashboard');

    Route::get('/albaranes', AlbaranesIndex::class)
        ->middleware('can:albaranes.ver_todos')
        ->name('albaranes.index');

    Route::get('/albaranes/crear', AlbaranesEditar::class)
        ->middleware('can:albaranes.crear_web')
        ->name('albaranes.crear');

    Route::get('/albaranes/{albaran}', AlbaranesVer::class)
        ->middleware('can:albaranes.ver_todos')
        ->name('albaranes.ver');

    Route::get('/albaranes/{albaran}/editar', AlbaranesEditar::class)
        ->middleware('can:albaranes.ver_todos')
        ->name('albaranes.editar');

    Route::get('/clientes', ClientesIndex::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.index');

    Route::get('/clientes/crear', ClientesEditar::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.crear');

    Route::get('/clientes/{cliente}', ClientesVer::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.ver');

    Route::get('/clientes/{cliente}/editar', ClientesEditar::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.editar');

    Route::get('/proyectos', ProyectosIndex::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.index');

    Route::get('/proyectos/crear', ProyectosEditar::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.crear');

    Route::get('/proyectos/grupos', GruposProyectosIndex::class)
        ->middleware('can:grupos_proyecto.ver')
        ->name('proyectos.grupos');

    Route::get('/proyectos/{proyecto}', ProyectosVer::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.ver');

    Route::get('/proyectos/{proyecto}/editar', ProyectosEditar::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.editar');

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
        ->middleware('can:configuracion.ver')
        ->name('configuracion.empresa');

    Route::get('/configuracion/ajustes', ConfiguracionAjustes::class)
        ->middleware('can:configuracion.ver')
        ->name('configuracion.ajustes');

    Route::get('/configuracion/roles', RolesIndex::class)
        ->middleware('can:roles.gestionar')
        ->name('configuracion.roles');

    Route::get('/perfil', MiPerfil::class)
        ->name('perfil.mi-perfil');
});
