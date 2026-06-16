<?php

use App\Http\Controllers\Api\ProyectoOpcionesController;
use App\Http\Controllers\Albaranes\ExportarPdfController as AlbaranesExportarPdf;
use App\Http\Controllers\Clientes\ExportarExcelController as ClientesExportarExcel;
use App\Http\Controllers\Clientes\ExportarPdfController as ClientesExportarPdf;
use App\Http\Controllers\Conceptos\ExportarExcelController as ConceptosExportarExcel;
use App\Http\Controllers\Conceptos\ExportarPdfController as ConceptosExportarPdf;
use App\Http\Controllers\Ausencias\ExportarExcelController as AusenciasExportarExcel;
use App\Http\Controllers\Ausencias\ExportarPdfController as AusenciasExportarPdf;
use App\Http\Controllers\Horas\ExportarExcelController as HorasExportarExcel;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Usuarios\ExportarExcelController as UsuariosExportarExcel;
use App\Http\Controllers\Usuarios\ExportarPdfController as UsuariosExportarPdf;
use App\Livewire\Albaranes\Editar as AlbaranesEditar;
use App\Livewire\Firma\FirmarAlbaran;
use App\Livewire\Albaranes\Index as AlbaranesIndex;
use App\Livewire\Albaranes\Ver as AlbaranesVer;
use App\Livewire\Borradores\Convertir as BorradoresConvertir;
use App\Livewire\Borradores\Editar as BorradoresEditar;
use App\Livewire\Borradores\Index as BorradoresIndex;
use App\Livewire\Ausencias\Index as AusenciasIndex;
use App\Livewire\Horas\Index as HorasIndex;
use App\Livewire\Incidencias\Index as IncidenciasIndex;
use App\Livewire\Borradores\Ver as BorradoresVer;
use App\Livewire\Clientes\Editar as ClientesEditar;
use App\Livewire\Clientes\Importar as ClientesImportar;
use App\Livewire\Clientes\Index as ClientesIndex;
use App\Livewire\Clientes\Ver as ClientesVer;
use App\Livewire\Conceptos\Importar as ConceptosImportar;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\Configuracion\Ajustes as ConfiguracionAjustes;
use App\Livewire\Configuracion\Api as ConfiguracionApi;
use App\Livewire\Configuracion\Licencias as ConfiguracionLicencias;
use App\Livewire\Configuracion\Logs as ConfiguracionLogs;
use App\Livewire\Empresa\Edit as EmpresaEdit;
use App\Livewire\Materiales\Editar as MaterialesEditar;
use App\Livewire\Materiales\Familias\Index as FamiliasIndex;
use App\Livewire\Materiales\Index as MaterialesIndex;
use App\Livewire\Materiales\NumeroPedidos\Index as NumeroPedidosIndex;
use App\Livewire\Materiales\Ver as MaterialesVer;
use App\Livewire\Pedidos\Editar as PedidosEditar;
use App\Livewire\Pedidos\Ver as PedidosVer;
use App\Livewire\Perfil\MiPerfil;
use App\Livewire\Proyectos\Editar as ProyectosEditar;
use App\Livewire\Proyectos\Grupos\Index as GruposProyectosIndex;
use App\Livewire\Proyectos\Index as ProyectosIndex;
use App\Livewire\Proyectos\Ver as ProyectosVer;
use App\Livewire\Roles\Index as RolesIndex;
use App\Livewire\Tarifas\Clientes\Index as TarifasClientesIndex;
use App\Livewire\Tarifas\Historial\Index as TarifasHistorialIndex;
use App\Livewire\Tarifas\Trabajadores\Index as TarifasTrabajadoresIndex;
use App\Livewire\Usuarios\Editar as UsuariosEditar;
use App\Livewire\Usuarios\Importar as UsuariosImportar;
use App\Livewire\Dashboard;
use App\Livewire\Usuarios\Index as UsuariosIndex;
use App\Livewire\Usuarios\Ver as UsuariosVer;
use Illuminate\Support\Facades\Route;

// API ligera — accesible desde móvil y web (solo necesita auth)
Route::middleware('auth')->group(function (): void {
    Route::get('/api/proyecto/{proyecto}/opciones', ProyectoOpcionesController::class)
        ->name('api.proyecto.opciones');
});

// Página pública de firma — accesible sin autenticación
Route::get('/firmar/{token}', FirmarAlbaran::class)->name('albaranes.firmar');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'ensure.web.access'])->group(function (): void {
    Route::get('/', Dashboard::class)->name('web.dashboard');

    Route::get('/ausencias', AusenciasIndex::class)
        ->middleware('can:ausencias.ver_todas')
        ->name('ausencias.index');

    Route::get('/ausencias/exportar/excel', AusenciasExportarExcel::class)
        ->middleware('can:ausencias.exportar')
        ->name('ausencias.exportar.excel');

    Route::get('/ausencias/exportar/pdf/{orientacion}', AusenciasExportarPdf::class)
        ->where('orientacion', 'vertical|horizontal')
        ->middleware('can:ausencias.exportar')
        ->name('ausencias.exportar.pdf');

    Route::get('/incidencias', IncidenciasIndex::class)
        ->middleware('can:incidencias.ver_todas')
        ->name('incidencias.index');

    Route::get('/horas', HorasIndex::class)
        ->name('horas.index');

    Route::get('/horas/exportar/excel', HorasExportarExcel::class)
        ->name('horas.exportar.excel');

    Route::get('/borradores', BorradoresIndex::class)
        ->middleware('can:borradores.ver_todos')
        ->name('borradores.index');

    Route::get('/borradores/crear', BorradoresEditar::class)
        ->middleware('can:borradores.crear_web')
        ->name('borradores.crear');

    Route::get('/borradores/{borrador}/convertir', BorradoresConvertir::class)
        ->middleware('can:borradores.convertir')
        ->name('borradores.convertir');

    Route::get('/borradores/{borrador}', BorradoresVer::class)
        ->middleware('can:borradores.ver_todos')
        ->name('borradores.ver');

    Route::get('/borradores/{borrador}/editar', BorradoresEditar::class)
        ->middleware('can:borradores.ver_todos')
        ->name('borradores.editar');

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

    Route::get('/albaranes/{albaran}/pdf', AlbaranesExportarPdf::class)
        ->middleware('can:albaranes.ver_todos')
        ->name('albaranes.pdf');

    Route::get('/clientes', ClientesIndex::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.index');

    Route::get('/clientes/crear', ClientesEditar::class)
        ->middleware('can:clientes.crear')
        ->name('clientes.crear');

    Route::get('/clientes/importar', ClientesImportar::class)
        ->middleware('can:clientes.importar')
        ->name('clientes.importar');

    Route::get('/clientes/exportar/excel', ClientesExportarExcel::class)
        ->middleware('can:clientes.exportar')
        ->name('clientes.exportar.excel');

    Route::get('/clientes/exportar/pdf/{orientacion}', ClientesExportarPdf::class)
        ->where('orientacion', 'vertical|horizontal')
        ->middleware('can:clientes.exportar')
        ->name('clientes.exportar.pdf');

    Route::get('/clientes/{cliente}', ClientesVer::class)
        ->middleware('can:clientes.ver')
        ->name('clientes.ver');

    Route::get('/clientes/{cliente}/editar', ClientesEditar::class)
        ->middleware('can:clientes.modificar')
        ->name('clientes.editar');

    Route::get('/proyectos', ProyectosIndex::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.index');

    Route::get('/proyectos/crear', ProyectosEditar::class)
        ->middleware('can:proyectos.crear')
        ->name('proyectos.crear');

    Route::get('/proyectos/grupos', GruposProyectosIndex::class)
        ->middleware('can:grupos_proyecto.ver')
        ->name('proyectos.grupos');

    Route::get('/proyectos/{proyecto}', ProyectosVer::class)
        ->middleware('can:proyectos.ver')
        ->name('proyectos.ver');

    Route::get('/proyectos/{proyecto}/editar', ProyectosEditar::class)
        ->middleware('can:proyectos.modificar')
        ->name('proyectos.editar');

    Route::middleware('modulo.materiales')->group(function (): void {
        Route::get('/materiales/pedidos', NumeroPedidosIndex::class)
            ->middleware('can:pedidos.ver')
            ->name('materiales.pedidos');

        Route::get('/materiales/pedidos/crear', PedidosEditar::class)
            ->middleware('can:pedidos.crear')
            ->name('pedidos.crear');

        Route::get('/materiales/pedidos/{pedido}', PedidosVer::class)
            ->middleware('can:pedidos.ver')
            ->name('pedidos.ver');

        Route::get('/materiales/pedidos/{pedido}/editar', PedidosEditar::class)
            ->middleware('can:pedidos.modificar')
            ->name('pedidos.editar');

        Route::get('/materiales/familias', FamiliasIndex::class)
            ->middleware('can:materiales.familias.ver')
            ->name('materiales.familias');

        Route::get('/materiales', MaterialesIndex::class)
            ->middleware('can:materiales.ver')
            ->name('materiales.index');

        Route::get('/materiales/crear', MaterialesEditar::class)
            ->middleware('can:materiales.crear')
            ->name('materiales.crear');

        Route::get('/materiales/{material}', MaterialesVer::class)
            ->middleware('can:materiales.ver')
            ->name('materiales.ver');

        Route::get('/materiales/{material}/editar', MaterialesEditar::class)
            ->middleware('can:materiales.modificar')
            ->name('materiales.editar');
    });

    Route::get('/usuarios', UsuariosIndex::class)
        ->middleware('can:usuarios.ver_todos')
        ->name('usuarios.index');

    Route::get('/usuarios/crear', UsuariosEditar::class)
        ->middleware('can:usuarios.ver_todos')
        ->name('usuarios.crear');

    Route::get('/usuarios/importar', UsuariosImportar::class)
        ->middleware('can:usuarios.importar')
        ->name('usuarios.importar');

    Route::get('/usuarios/exportar/excel', UsuariosExportarExcel::class)
        ->middleware('can:usuarios.exportar')
        ->name('usuarios.exportar.excel');

    Route::get('/usuarios/exportar/pdf/{orientacion}', UsuariosExportarPdf::class)
        ->where('orientacion', 'vertical|horizontal')
        ->middleware('can:usuarios.exportar')
        ->name('usuarios.exportar.pdf');

    Route::get('/usuarios/{usuario}', UsuariosVer::class)
        ->middleware('can:usuarios.ver_todos')
        ->name('usuarios.ver');

    Route::get('/usuarios/{usuario}/editar', UsuariosEditar::class)
        ->middleware('can:usuarios.ver_todos')
        ->name('usuarios.editar');

    Route::get('/conceptos', ConceptosIndex::class)
        ->middleware('can:conceptos.ver')
        ->name('conceptos.index');

    Route::get('/conceptos/importar', ConceptosImportar::class)
        ->middleware('can:conceptos.importar')
        ->name('conceptos.importar');

    Route::get('/conceptos/exportar/excel', ConceptosExportarExcel::class)
        ->middleware('can:conceptos.exportar')
        ->name('conceptos.exportar.excel');

    Route::get('/conceptos/exportar/pdf/{orientacion}', ConceptosExportarPdf::class)
        ->where('orientacion', 'vertical|horizontal')
        ->middleware('can:conceptos.exportar')
        ->name('conceptos.exportar.pdf');

    // ── Tarifas (v2) ───────────────────────────────────────────────
    Route::get('/tarifas/clientes', TarifasClientesIndex::class)
        ->middleware('can:tarifas.ver')
        ->name('tarifas.clientes');

    Route::get('/tarifas/trabajadores', TarifasTrabajadoresIndex::class)
        ->middleware('can:tarifas.ver')
        ->name('tarifas.trabajadores');

    Route::get('/tarifas/historial', TarifasHistorialIndex::class)
        ->middleware('can:tarifas.historial_ver')
        ->name('tarifas.historial');

    Route::get('/configuracion/empresa', EmpresaEdit::class)
        ->middleware('can:empresa.ver')
        ->name('configuracion.empresa');

    Route::get('/configuracion/ajustes', ConfiguracionAjustes::class)
        ->middleware('can:ajustes.ver')
        ->name('configuracion.ajustes');

    Route::get('/configuracion/roles', RolesIndex::class)
        ->middleware('can:roles.ver')
        ->name('configuracion.roles');

    Route::get('/configuracion/api', ConfiguracionApi::class)
        ->middleware('can:api.ver')
        ->name('configuracion.api');

    Route::get('/configuracion/logs', ConfiguracionLogs::class)
        ->middleware('can:logs.ver')
        ->name('configuracion.logs');

    Route::get('/configuracion/licencias', ConfiguracionLicencias::class)
        ->middleware('can:licencias.ver')
        ->name('configuracion.licencias');

    Route::get('/perfil', MiPerfil::class)
        ->name('perfil.mi-perfil');
});
