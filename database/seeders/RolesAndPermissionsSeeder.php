<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'albaranes.ver_todos',
            'albaranes.ver_propios',
            'albaranes.crear_movil',
            'albaranes.crear_web',
            'albaranes.modificar',
            'albaranes.modificar_terminado',
            'albaranes.firmar',
            'albaranes.imprimir',
            'albaranes.exportar',
            'usuarios.ver_todos',
            'usuarios.crear_superadmin',
            'usuarios.crear_administrador',
            'usuarios.crear_trabajador',
            'usuarios.crear_responsable',
            'usuarios.modificar',
            'usuarios.eliminar',
            'clientes.ver',
            'clientes.crear',
            'clientes.modificar',
            'clientes.eliminar',
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.modificar',
            'proyectos.eliminar',
            'tipos_proyecto.ver',
            'tipos_proyecto.crear',
            'tipos_proyecto.modificar',
            'tipos_proyecto.eliminar',
            'materiales.ver',
            'materiales.crear',
            'materiales.modificar',
            'materiales.eliminar',
            'stock.entrada',
            'stock.ajustar',
            'ausencias.ver_todas',
            'ausencias.ver_propias',
            'ausencias.solicitar',
            'ausencias.aprobar',
            'incidencias.ver_todas',
            'incidencias.ver_propias',
            'incidencias.crear',
            'incidencias.modificar',
            'configuracion.empresa',
            'configuracion.numeracion_albaran',
            'roles.gestionar',
            'permisos.gestionar',
            'logs.ver',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
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

        $superadmin->syncPermissions(Permission::all());

        $administrador->syncPermissions([
            'albaranes.ver_todos',
            'albaranes.crear_web',
            'albaranes.modificar',
            'albaranes.imprimir',
            'albaranes.exportar',
            'usuarios.ver_todos',
            'usuarios.crear_administrador',
            'usuarios.crear_trabajador',
            'usuarios.crear_responsable',
            'usuarios.modificar',
            'clientes.ver',
            'clientes.crear',
            'clientes.modificar',
            'clientes.eliminar',
            'proyectos.ver',
            'proyectos.crear',
            'proyectos.modificar',
            'proyectos.eliminar',
            'tipos_proyecto.ver',
            'tipos_proyecto.crear',
            'tipos_proyecto.modificar',
            'tipos_proyecto.eliminar',
            'materiales.ver',
            'materiales.crear',
            'materiales.modificar',
            'materiales.eliminar',
            'stock.entrada',
            'stock.ajustar',
            'ausencias.ver_todas',
            'ausencias.aprobar',
            'incidencias.ver_todas',
            'incidencias.modificar',
            'configuracion.empresa',
            'configuracion.numeracion_albaran',
            'logs.ver',
        ]);

        $trabajador->syncPermissions([
            'albaranes.ver_propios',
            'albaranes.crear_movil',
            'albaranes.firmar',
            'ausencias.ver_propias',
            'ausencias.solicitar',
            'incidencias.ver_propias',
            'incidencias.crear',
        ]);

        $responsable->syncPermissions([
            'albaranes.ver_propios',
            'albaranes.firmar',
        ]);
    }
}
