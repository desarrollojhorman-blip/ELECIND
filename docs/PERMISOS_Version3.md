# 🔐 PERMISOS Y ROLES — ELECIND

Sistema híbrido: roles del sistema + permisos granulares + niveles jerárquicos.

## 👤 Roles del sistema (predefinidos)

| Rol | Nivel | Acceso | tipo_usuario | Descripción |
|---|---|---|---|---|
| `superadmin` | 100 | Web + Móvil | interno | Técnico. Visión global. Configura todo. |
| `administrador` | 50 | Solo Web | interno | Gestiona empresa. Crea usuarios, albaranes, etc. |
| `trabajador` | 10 | Solo Móvil | interno | Crea albaranes, firma, ausencias. |
| `responsable` | 10 | Solo Móvil / Token | externo | Empleado del cliente. Firma albaranes. |

## 🔑 Login

- **Por `username`** (NO por email).
- Razón: permitimos emails duplicados, así que el username es la clave única.
- Los responsables externos pueden NO tener cuenta activa (firman solo por token email).

## 📊 Reglas de jerarquía

1. **Crear usuarios**: solo puedes asignar roles de **nivel ≤ al tuyo**.
2. **Ver usuarios**: solo ves usuarios de nivel ≤ al tuyo.
3. **Editar permisos**: solo puedes asignar permisos que tú tienes.

**Ejemplos:**
- Superadmin (100) → puede crear superadmins, admins, trabajadores, responsables.
- Administrador (50) → puede crear admins, trabajadores, responsables. NO puede crear superadmins. NO ve a los superadmins en listados.
- Trabajador (10) → no puede crear usuarios.

## 🎯 Permisos granulares

### Albaranes
```
albaranes.ver_todos
albaranes.ver_propios
albaranes.crear_movil
albaranes.crear_web
albaranes.modificar
albaranes.modificar_terminado
albaranes.desbloquear
albaranes.eliminar
albaranes.firmar
albaranes.imprimir
albaranes.exportar
albaranes.cambiar_estado
albaranes.adjuntar_factura
```

### Usuarios
```
usuarios.ver_todos
usuarios.crear_superadmin
usuarios.crear_administrador
usuarios.crear_trabajador
usuarios.crear_responsable
usuarios.modificar
usuarios.eliminar
usuarios.activar_desactivar
```

### Clientes y proyectos
```
clientes.ver / crear / modificar / eliminar
proyectos.ver / crear / modificar / eliminar
tipos_proyecto.ver / crear / modificar / eliminar
conceptos.ver / crear / modificar / eliminar
```

### Materiales y stock
```
materiales.ver / crear / modificar / eliminar
stock.entrada
stock.ajustar
stock.ver_historico
```

### Ausencias
```
ausencias.ver_todas
ausencias.ver_propias
ausencias.solicitar
ausencias.aprobar
ausencias.modificar
ausencias.modificar_aprobada
```

### Incidencias
```
incidencias.ver_todas
incidencias.ver_propias
incidencias.crear
incidencias.modificar
incidencias.cambiar_estado
incidencias.asignar
```

### Configuración y técnico
```
configuracion.empresa
configuracion.notificaciones
configuracion.numeracion_albaran
configuracion.plantilla_pdf
configuracion.estados_configurables
roles.gestionar
permisos.gestionar
logs.ver
api.gestionar
licencia.ver
backups.gestionar
```

## 🛠️ Implementación

**Stack:** `spatie/laravel-permission` + lógica custom de niveles.

### Estructura
```php
// Migración roles (extendida)
Schema::table('roles', function ($table) {
    $table->integer('nivel')->default(10);
    $table->enum('acceso', ['web', 'movil', 'ambos'])->default('web');
    $table->boolean('es_sistema')->default(false);
});
```

### Middleware
```php
// EnsureWebAccess.php
public function handle($request, $next) {
    $acceso = auth()->user()->role->acceso;
    abort_if(!in_array($acceso, ['web', 'ambos']), 403);
    return $next($request);
}
```

### Helper en Blade
```blade
@can('albaranes.modificar_terminado')
    <button>Desbloquear</button>
@endcan

@role('superadmin')
    <a href="...">Panel técnico</a>
@endrole
```

### Filtrado en queries
```php
if (auth()->user()->can('albaranes.ver_todos')) {
    $q = Albaran::query();
} else {
    $q = Albaran::whereHas('participantes', 
        fn($q) => $q->where('usuario_id', auth()->id())
    );
}
```

## 🎨 CRUD de roles personalizados (web)

Desde Fase 1 habrá pantalla en web:

- Listar roles.
- Crear rol nuevo (nombre, nivel, acceso, checkboxes de permisos).
- Modificar rol (solo si no es `es_sistema=true`).
- Eliminar rol (solo si no tiene usuarios asignados).

**Filtros automáticos:**
- Al crear rol → solo puedes seleccionar nivel ≤ al tuyo.
- Al crear rol → solo puedes asignar permisos que tienes.

## 🔒 Casos especiales

### Stock bajo
- Notificación → solo a **admin** del tenant (no a superadmin), para no saturar.
- Configurable: el admin puede desactivar.

### Modificar tras "terminado" o "facturado"
- Requiere permiso `albaranes.modificar_terminado`.
- Modal de confirmación.
- Email automático a la empresa.
- Quedará registrado en activity log.

### Token email de firma
- No requiere autenticación.
- Acceso limitado a: ver datos del albarán + firmar SU campo.
- Caduca a 7 días (configurable) O al firmar (lo que ocurra primero).

### Eliminación de usuarios
- Siempre **soft delete**.
- Albaranes conservan snapshot del nombre/DNI/CIF.
- Empresa cliente no se puede eliminar si tiene albaranes (debe archivar antes).

### Límite de usuarios por plan (Fase 6)
- Solo cuentan los `tipo_usuario = 'interno'`.
- Los responsables externos NO consumen licencia.