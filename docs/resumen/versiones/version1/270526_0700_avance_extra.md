# Avance extra — 27/05/2026

## 1. Parte personalizado (móvil) → guarda como Borrador

### Qué se hizo
- El formulario de parte personalizado en móvil (`/m/albaranes/personalizado`) ya **no crea un Albarán**, sino un **Borrador** con número automático tipo `BOR-0001`.
- El flujo es: rellenar el formulario → guardar → modal de confirmación → volver al inicio. No hay paso de firma.

### Cascada de selección
1. **Cliente** — todos los clientes activos del sistema. Formato: `id · nombre`. Opción "Otro" para texto libre.
2. **Proyecto** — filtrado por el cliente seleccionado. Si no hay cliente, lista vacía (no bloqueado). Opción "Otro" para texto libre. Si el cliente es texto libre, el proyecto se fuerza a texto libre.
3. **Concepto** — todos los conceptos del sistema. Opción "Otro" para texto libre.
4. **Responsable** — filtrado por `users.cliente_id` (responsables ligados al cliente seleccionado). Opción "Otro" para texto libre.
5. **Compañeros** — todos los trabajadores activos del sistema (excluyendo al usuario actual). Formato: `nº empleado · nombre apellidos`.
6. **Materiales** — todos los materiales activos del sistema.

### Permisos
- Permiso nuevo: `borradores.crear_movil` (ámbito: móvil)
- El rol `trabajador` tiene: `borradores.ver_propios` + `borradores.crear_movil`
- Ruta `/m/albaranes/personalizado` protegida con `can:borradores.crear_movil`

### Pendiente / a tener en cuenta
- Los borradores creados desde móvil aparecen en la lista web de Borradores y se pueden convertir a albarán desde ahí.
- El responsable solo aparece si el cliente tiene usuarios con `cliente_id` coincidente y rol `responsable`. Si el cliente no tiene responsables en el sistema, la lista estará vacía (se puede escribir a mano).

---

## 2. Sección Configuración → API

### Qué se hizo
- Nueva página en `/configuracion/api` con CRUD completo de tokens de API externos.
- Tabla: Aplicación · Descripción · Token (enmascarado) · Estado · Acciones.
- Modal crear/editar: Nombre → Token → Descripción → Activo.
- Campo token con botón **Generar** (aleatorio 64 chars) y botón **ojo** para mostrar/ocultar, estilo igual que el campo contraseña en usuarios.
- Modal ver: muestra el token completo con toggle ojo.
- Modal eliminar: confirmación con advertencia de que es definitivo.
- Botón "Nuevo" en verde, botones Editar en azul y Eliminar en rojo.

### Modelo y tabla
- Tabla: `api_tokens` — campos: `id`, `nombre`, `descripcion`, `token` (único, max 80), `activo`, `creado_por` (FK users), `timestamps`.
- Modelo: `App\Models\ApiToken` con método `tokenMascarado()` y relación `creador()`.

### Permisos
- Ver la página: `configuracion.ver`
- Crear / editar / eliminar tokens: `api_tokens.gestionar`
- `api_tokens.gestionar` es **solo superadmin por defecto** (excluido de administrador, igual que `materiales.gestionar_precios`).
- Los botones Nuevo / Editar / Eliminar están envueltos en `@can('api_tokens.gestionar')`.
- Las acciones del servidor (guardar, eliminar) también comprueban `Gate::authorize('api_tokens.gestionar')`.

---

## 3. Sección Configuración → Logs

Placeholder — página creada con estructura base lista para implementar el visor de actividad.  
Ruta: `/configuracion/logs` · Permiso: `configuracion.ver`.

---

## 4. Sección Configuración → Licencias

### Qué se hizo
- Página en `/configuracion/licencias` con información de licencia estilo documento.
- **Datos empresa**: NIF, Razón Social, Dirección, CP, Población, Teléfono, Email — leídos desde `Empresa::actual()` (misma fuente que el resto de la app).
- **Producto licenciado**: Nombre producto, Fabricante (Entreredes Consultoría Tecnológica SL), Fecha de licencia, Versión.
- **Módulos incluidos**: lista de los 8 módulos con estado Incluido/No disponible.
- Los datos del producto (`licencia[]`) están hardcodeados en `App\Livewire\Configuracion\Licencias.php` — fácil de editar o conectar a config/BD más adelante.
- Ruta: `/configuracion/licencias` · Permiso: `configuracion.ver`.

---

## 5. Permisos y roles — revisión general

### Correcciones aplicadas
| Problema | Archivo | Solución |
|---|---|---|
| `Personalizado.php` usaba gate de Albarán | `Personalizado.php` | Cambiado a `Gate::authorize('create', Borrador::class)` |
| `borradores.crear` era un único permiso para ambos ámbitos | Seeder | Dividido en `borradores.crear_movil` + `borradores.crear_web` |
| Trabajador no tenía permisos de borradores | Seeder | Añadidos `borradores.ver_propios` + `borradores.crear_movil` |
| Ruta móvil personalizado usaba permiso de albaranes | `routes/mobile.php` | Cambiado a `can:borradores.crear_movil` |
| Ruta web borradores/crear usaba permiso antiguo | `routes/web.php` | Cambiado a `can:borradores.crear_web` |
| Botones API sin control de permisos | `api.blade.php` | Envueltos en `@can('api_tokens.gestionar')` |

### Estado actual de permisos por rol (resumen de lo relevante)

| Permiso | superadmin | administrador | trabajador | responsable |
|---|:---:|:---:|:---:|:---:|
| `borradores.ver_todos` | ✓ | ✓ | — | — |
| `borradores.ver_propios` | ✓ | ✓ | ✓ | — |
| `borradores.crear_movil` | ✓ | ✓ | ✓ | — |
| `borradores.crear_web` | ✓ | ✓ | — | — |
| `borradores.modificar` | ✓ | ✓ | — | — |
| `borradores.convertir` | ✓ | ✓ | — | — |
| `configuracion.ver` | ✓ | ✓ | — | — |
| `configuracion.editar` | ✓ | ✓ | — | — |
| `api_tokens.gestionar` | ✓ | — | — | — |

---

## 6. Cosas a tener en cuenta / pendientes

### Después de cada despliegue
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
```

### Datos de licencias
Los datos en `App\Livewire\Configuracion\Licencias.php` (clave, plan, fechas, versión) están hardcodeados en el método `licencia()`. Hay que actualizarlos manualmente o conectarlos a una tabla/config cuando se formalice el sistema de licencias.

### Módulos en Licencias
El array `modulos()` en `Licencias.php` también está hardcodeado. Si se añaden nuevos módulos a la app, hay que añadirlos aquí manualmente.

### Logs
La sección Logs es un placeholder. Cuando se implemente, usar el permiso `logs.ver` que ya existe en el seeder (ámbito `ambos`).

### Responsable en parte personalizado
Los responsables del selector dependen de `users.cliente_id`. Si un cliente no tiene usuarios con ese FK y rol `responsable`, la lista saldrá vacía y el operario deberá escribirlo a mano. Revisar que los responsables estén bien asignados en la ficha de usuario.
