<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Limpiar permisos renombrados o eliminados en versiones anteriores.
        Permission::whereIn('name', [
            'configuracion.empresa',
            'configuracion.numeracion_albaran',
            'tipos_proyecto.ver',
            'tipos_proyecto.crear',
            'tipos_proyecto.modificar',
            'tipos_proyecto.eliminar',
            'borradores.crear', // reemplazado por borradores.crear_movil + borradores.crear_web
        ])->delete();

        $permisos = $this->catalogoPermisos();

        foreach ($permisos as $permiso) {
            Permission::updateOrCreate(
                ['name' => $permiso['name'], 'guard_name' => 'web'],
                [
                    'ambito' => $permiso['ambito'],
                    'descripcion' => $permiso['descripcion'],
                    'categoria' => $permiso['categoria'],
                ]
            );
        }

        $superadmin = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'web'],
            ['nivel' => 100, 'acceso' => 'ambos', 'es_sistema' => true]
        );

        $administrador = Role::firstOrCreate(
            ['name' => 'administrador', 'guard_name' => 'web'],
            ['nivel' => 50, 'acceso' => 'web', 'es_sistema' => true]
        );

        $trabajador = Role::firstOrCreate(
            ['name' => 'trabajador', 'guard_name' => 'web'],
            ['nivel' => 10, 'acceso' => 'movil', 'es_sistema' => true]
        );

        $responsable = Role::firstOrCreate(
            ['name' => 'responsable', 'guard_name' => 'web'],
            ['nivel' => 10, 'acceso' => 'movil', 'es_sistema' => true]
        );

        // Superadmin: todos los permisos.
        $superadmin->syncPermissions(Permission::all());

        // Administrador: todos los de ámbito web salvo los de superadmin estrictos.
        $administrador->syncPermissions(Permission::query()
            ->whereIn('ambito', ['web', 'ambos'])
            ->whereNotIn('name', [
                'usuarios.crear_superadmin',
                'albaranes.invalidar_firma', // sólo superadmin borra firmas (auditoría legal)
                'clientes.gestionar_papelera', // por defecto solo superadmin gestiona la papelera de clientes
                'usuarios.gestionar_papelera', // por defecto solo superadmin gestiona la papelera de usuarios
                'conceptos.gestionar_papelera', // por defecto solo superadmin gestiona la papelera de conceptos
                'usuarios.gestionar_tarifas',   // por defecto solo superadmin ve/modifica las tasas (dato sensible)
                'materiales.gestionar_precios', // por defecto solo superadmin ve/modifica los precios (dato sensible)
                'api_tokens.gestionar',         // por defecto solo superadmin gestiona los tokens de API externos
            ])
            ->pluck('name')
            ->all());

        // Trabajador: permisos móviles operativos.
        $trabajador->syncPermissions([
            'borradores.ver_propios',
            'borradores.crear_movil',
            'albaranes.ver_propios',
            'albaranes.crear_movil',
            'albaranes.firmar',
            'albaranes.descargar_pdf',
            'ausencias.ver_propias',
            'ausencias.solicitar',
            'incidencias.ver_propias',
            'incidencias.crear',
        ]);

        // Responsable: firma desde móvil o token email.
        $responsable->syncPermissions([
            'albaranes.ver_propios',
            'albaranes.firmar',
            'albaranes.descargar_pdf',
        ]);
    }

    /**
     * Catálogo completo de permisos con ámbito y categoría.
     *
     * @return array<int, array{name: string, ambito: string, descripcion: string, categoria: string}>
     */
    private function catalogoPermisos(): array
    {
        return [
            // ─────────────── Borradores ───────────────
            ['name' => 'borradores.ver_todos',   'ambito' => 'web',   'descripcion' => 'Ver todos los borradores de la empresa',        'categoria' => 'borradores'],
            ['name' => 'borradores.ver_propios', 'ambito' => 'ambos', 'descripcion' => 'Ver SOLO mis borradores',                            'categoria' => 'borradores'],
            ['name' => 'borradores.crear_movil', 'ambito' => 'movil', 'descripcion' => 'Crear borradores desde móvil (parte personalizado)', 'categoria' => 'borradores'],
            ['name' => 'borradores.crear_web',   'ambito' => 'web',   'descripcion' => 'Crear borradores desde web',                         'categoria' => 'borradores'],
            ['name' => 'borradores.modificar',   'ambito' => 'web',   'descripcion' => 'Modificar borradores de otros',                      'categoria' => 'borradores'],
            ['name' => 'borradores.convertir',   'ambito' => 'web',   'descripcion' => 'Convertir borradores a albarán oficial',             'categoria' => 'borradores'],

            // ─────────────── Albaranes ───────────────
            ['name' => 'albaranes.ver_todos', 'ambito' => 'ambos', 'descripcion' => 'Ver albaranes de TODA la empresa (no solo los propios)', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.ver_propios', 'ambito' => 'movil', 'descripcion' => 'Ver SOLO los albaranes en los que participo', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.crear_movil', 'ambito' => 'movil', 'descripcion' => 'Crear albaranes desde móvil', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.crear_web', 'ambito' => 'web', 'descripcion' => 'Crear albaranes desde web', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar albaranes', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.modificar_terminado', 'ambito' => 'web', 'descripcion' => 'Modificar albaranes terminados', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.firmar', 'ambito' => 'movil', 'descripcion' => 'Firmar albaranes', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.imprimir', 'ambito' => 'web', 'descripcion' => 'Imprimir albaranes (PDF)', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar albaranes a Excel', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.descargar_pdf', 'ambito' => 'ambos', 'descripcion' => 'Descargar PDF firmado del albarán', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.solicitar_firma', 'ambito' => 'web', 'descripcion' => 'Generar y enviar tokens de firma por email', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.invalidar_firma', 'ambito' => 'web', 'descripcion' => 'Eliminar firmas registradas (queda en activity log)', 'categoria' => 'albaranes'],
            ['name' => 'albaranes.facturar', 'ambito' => 'web', 'descripcion' => 'Marcar albaranes como facturados', 'categoria' => 'albaranes'],

            // ─────────────── Usuarios ───────────────
            ['name' => 'usuarios.ver_todos', 'ambito' => 'web', 'descripcion' => 'Ver lista de usuarios', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.crear_superadmin', 'ambito' => 'web', 'descripcion' => 'Crear usuarios con rol superadmin', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.crear_administrador', 'ambito' => 'web', 'descripcion' => 'Crear usuarios con rol administrador', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.crear_trabajador', 'ambito' => 'web', 'descripcion' => 'Crear usuarios con rol trabajador', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.crear_responsable', 'ambito' => 'web', 'descripcion' => 'Crear usuarios con rol responsable', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar usuarios', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar usuarios', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar usuarios a Excel', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.importar', 'ambito' => 'web', 'descripcion' => 'Importar usuarios desde Excel/CSV', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.imprimir', 'ambito' => 'web', 'descripcion' => 'Imprimir lista de usuarios', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.gestionar_papelera', 'ambito' => 'web', 'descripcion' => 'Ver papelera de usuarios y restaurar eliminados', 'categoria' => 'usuarios'],
            ['name' => 'usuarios.gestionar_tarifas', 'ambito' => 'web', 'descripcion' => 'Ver y modificar las tasas (€/hora) de los usuarios', 'categoria' => 'usuarios'],

            // ─────────────── Clientes ───────────────
            ['name' => 'clientes.ver', 'ambito' => 'web', 'descripcion' => 'Ver clientes', 'categoria' => 'clientes'],
            ['name' => 'clientes.crear', 'ambito' => 'web', 'descripcion' => 'Crear clientes', 'categoria' => 'clientes'],
            ['name' => 'clientes.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar clientes', 'categoria' => 'clientes'],
            ['name' => 'clientes.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar clientes', 'categoria' => 'clientes'],
            ['name' => 'clientes.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar clientes a Excel', 'categoria' => 'clientes'],
            ['name' => 'clientes.importar', 'ambito' => 'web', 'descripcion' => 'Importar clientes desde Excel/CSV', 'categoria' => 'clientes'],
            ['name' => 'clientes.imprimir', 'ambito' => 'web', 'descripcion' => 'Imprimir lista de clientes', 'categoria' => 'clientes'],
            ['name' => 'clientes.gestionar_papelera', 'ambito' => 'web', 'descripcion' => 'Ver papelera de clientes y restaurar eliminados', 'categoria' => 'clientes'],

            // ─────────────── Proyectos ───────────────
            ['name' => 'proyectos.ver', 'ambito' => 'web', 'descripcion' => 'Ver proyectos', 'categoria' => 'proyectos'],
            ['name' => 'proyectos.crear', 'ambito' => 'web', 'descripcion' => 'Crear proyectos', 'categoria' => 'proyectos'],
            ['name' => 'proyectos.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar proyectos', 'categoria' => 'proyectos'],
            ['name' => 'proyectos.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar proyectos', 'categoria' => 'proyectos'],
            ['name' => 'proyectos.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar proyectos a Excel', 'categoria' => 'proyectos'],
            ['name' => 'proyectos.imprimir', 'ambito' => 'web', 'descripcion' => 'Imprimir lista de proyectos', 'categoria' => 'proyectos'],

            // ─────────────── Grupos de proyecto ───────────────
            ['name' => 'grupos_proyecto.ver', 'ambito' => 'web', 'descripcion' => 'Ver grupos de proyecto', 'categoria' => 'grupos_proyecto'],
            ['name' => 'grupos_proyecto.crear', 'ambito' => 'web', 'descripcion' => 'Crear grupos de proyecto', 'categoria' => 'grupos_proyecto'],
            ['name' => 'grupos_proyecto.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar grupos de proyecto', 'categoria' => 'grupos_proyecto'],
            ['name' => 'grupos_proyecto.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar grupos de proyecto', 'categoria' => 'grupos_proyecto'],

            // ─────────────── Nº Pedido ───────────────
            ['name' => 'pedidos.ver', 'ambito' => 'web', 'descripcion' => 'Ver números de pedido', 'categoria' => 'materiales'],
            ['name' => 'pedidos.crear', 'ambito' => 'web', 'descripcion' => 'Crear números de pedido', 'categoria' => 'materiales'],
            ['name' => 'pedidos.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar números de pedido', 'categoria' => 'materiales'],
            ['name' => 'pedidos.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar números de pedido', 'categoria' => 'materiales'],

            // ─────────────── Materiales ───────────────
            ['name' => 'materiales.ver', 'ambito' => 'web', 'descripcion' => 'Ver materiales', 'categoria' => 'materiales'],
            ['name' => 'materiales.crear', 'ambito' => 'web', 'descripcion' => 'Crear materiales', 'categoria' => 'materiales'],
            ['name' => 'materiales.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar materiales', 'categoria' => 'materiales'],
            ['name' => 'materiales.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar materiales', 'categoria' => 'materiales'],
            ['name' => 'materiales.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar materiales a Excel', 'categoria' => 'materiales'],
            ['name' => 'materiales.imprimir', 'ambito' => 'web', 'descripcion' => 'Imprimir lista de materiales', 'categoria' => 'materiales'],
            ['name' => 'materiales.gestionar_precios', 'ambito' => 'web', 'descripcion' => 'Ver y modificar los precios (coste/venta) de los materiales', 'categoria' => 'materiales'],

            // ─────────────── Familias de material ───────────────
            ['name' => 'materiales.familias.ver', 'ambito' => 'web', 'descripcion' => 'Ver familias de material', 'categoria' => 'materiales'],
            ['name' => 'materiales.familias.crear', 'ambito' => 'web', 'descripcion' => 'Crear familias de material', 'categoria' => 'materiales'],
            ['name' => 'materiales.familias.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar familias de material', 'categoria' => 'materiales'],
            ['name' => 'materiales.familias.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar familias de material', 'categoria' => 'materiales'],

            // ─────────────── Conceptos ───────────────
            ['name' => 'conceptos.ver', 'ambito' => 'web', 'descripcion' => 'Ver conceptos', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.crear', 'ambito' => 'web', 'descripcion' => 'Crear conceptos', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar conceptos', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.eliminar', 'ambito' => 'web', 'descripcion' => 'Eliminar conceptos', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.gestionar_papelera', 'ambito' => 'web', 'descripcion' => 'Ver papelera de conceptos y restaurar eliminados', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar conceptos a Excel/PDF', 'categoria' => 'conceptos'],
            ['name' => 'conceptos.importar', 'ambito' => 'web', 'descripcion' => 'Importar conceptos desde Excel/CSV', 'categoria' => 'conceptos'],

            // ─────────────── Stock ───────────────
            ['name' => 'stock.entrada', 'ambito' => 'web', 'descripcion' => 'Registrar entradas de stock', 'categoria' => 'stock'],
            ['name' => 'stock.ajustar', 'ambito' => 'web', 'descripcion' => 'Ajustar stock (mermas/inventario)', 'categoria' => 'stock'],

            // ─────────────── Ausencias ───────────────
            ['name' => 'ausencias.ver_todas', 'ambito' => 'web', 'descripcion' => 'Ver ausencias de TODA la empresa (no solo las propias)', 'categoria' => 'ausencias'],
            ['name' => 'ausencias.ver_propias', 'ambito' => 'movil', 'descripcion' => 'Ver SOLO mis ausencias', 'categoria' => 'ausencias'],
            ['name' => 'ausencias.solicitar', 'ambito' => 'movil', 'descripcion' => 'Solicitar ausencias', 'categoria' => 'ausencias'],
            ['name' => 'ausencias.aprobar', 'ambito' => 'web', 'descripcion' => 'Aprobar/rechazar ausencias', 'categoria' => 'ausencias'],
            ['name' => 'ausencias.exportar', 'ambito' => 'web', 'descripcion' => 'Exportar ausencias a Excel/PDF', 'categoria' => 'ausencias'],

            // ─────────────── Incidencias ───────────────
            ['name' => 'incidencias.ver_todas', 'ambito' => 'web', 'descripcion' => 'Ver incidencias de TODA la empresa (no solo las propias)', 'categoria' => 'incidencias'],
            ['name' => 'incidencias.ver_propias', 'ambito' => 'movil', 'descripcion' => 'Ver SOLO mis incidencias', 'categoria' => 'incidencias'],
            ['name' => 'incidencias.crear', 'ambito' => 'movil', 'descripcion' => 'Crear incidencias', 'categoria' => 'incidencias'],
            ['name' => 'incidencias.modificar', 'ambito' => 'web', 'descripcion' => 'Modificar incidencias', 'categoria' => 'incidencias'],

            // ─────────────── Configuración ───────────────
            ['name' => 'configuracion.ver',    'ambito' => 'web', 'descripcion' => 'Ver configuración (empresa, ajustes, licencias y logs)',       'categoria' => 'configuracion'],
            ['name' => 'configuracion.editar', 'ambito' => 'web', 'descripcion' => 'Editar configuración (empresa y ajustes)',                     'categoria' => 'configuracion'],
            ['name' => 'api_tokens.gestionar', 'ambito' => 'web', 'descripcion' => 'Crear, editar y eliminar tokens de API externos (dato sensible)', 'categoria' => 'configuracion'],

            // ─────────────── Roles y permisos ───────────────
            ['name' => 'roles.gestionar', 'ambito' => 'web', 'descripcion' => 'Gestionar roles personalizados', 'categoria' => 'roles'],
            ['name' => 'permisos.gestionar', 'ambito' => 'web', 'descripcion' => 'Gestionar permisos (avanzado)', 'categoria' => 'roles'],

            // ─────────────── Sistema ───────────────
            ['name' => 'logs.ver', 'ambito' => 'ambos', 'descripcion' => 'Ver registro de actividad', 'categoria' => 'sistema'],
        ];
    }
}
