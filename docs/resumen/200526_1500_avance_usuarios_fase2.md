# Resumen de Sesión — 2026-05-20 (tarde) · Usuarios (import / export / papelera / nº empleado)

Fecha: 2026-05-20 ~15:00
Estado: **Funcionalidad completa**, pendiente de prueba en navegador por el usuario.
Ámbito: Web. Fase 5 (import/export) — replicación del patrón de Clientes a Usuarios.

> Detalle previo: [`190526_1500_clientes_fase_2.md`](./190526_1500_clientes_fase_2.md) (importación de Clientes),
> [`190526_1500_exportar_fase_2.md`](./190526_1500_exportar_fase_2.md) (reglas técnicas), y
> [`200526_0900_clientes_cambios_librerias_fase2.md`](./200526_0900_clientes_cambios_librerias_fase2.md) (Telescope + Flatpickr).

---

## 1. Qué se ha construido

Aplicado a **Usuarios** el mismo patrón que ya teníamos en Clientes, con los matices propios del modelo (login con username + password + rol + tipo interno/externo).

### 1.1 Campo nuevo `numero_empleado`
- Migración `2026_05_20_120000_add_numero_empleado_to_users`: `VARCHAR(50) NULL` en `users`.
- Texto libre, no único, opcional. Solo información HR-side (cada empresa lo nombra a su manera).
- Añadido al `fillable` del modelo `User` y al modal de crear/editar usuario (en la sección "Datos personales").

### 1.2 `id` como código de usuario visible
- Decisión: **no creamos un código artificial** para Usuarios (como sí hicimos en Clientes con `codigo_cliente`).
- El `id` autoincremental ya cumple: único, automático, inmutable, no-importable.
- Aparece visible en el PDF y en el listado donde tenga sentido.

### 1.3 Validación con fuente única `UserFields`
- Nueva clase `app/Support/UserFields.php` (espejo de `ClienteFields`).
- `getValidationRules()` devuelve reglas base para todos los campos.
- `uniqueFields()` devuelve `['username']` — solo el login es único. Email/DNI/CIF/teléfono/nº empleado pueden repetirse.
- `UserForm::rules()` refactorizado para leer de ahí + añadir los `Rule::unique` dinámicos y los condicionales (password required al crear, cliente_id required si externo).

### 1.4 Bloqueo al eliminar (`UserPolicy::delete`)
- Devuelve `Response::deny(mensaje)` si el usuario tiene:
  - **Proyectos vinculados**: asignado (pivot `proyecto_usuario`) o responsable principal de algún proyecto.
  - **Albaranes vinculados**: creados (`creador_id`) o aparece en líneas (`albaran_lineas_personal.usuario_id`).
- Mensaje conjugado: *"No puedes eliminar el usuario «pepe» porque tiene 3 proyectos y 5 albaranes vinculados."*
- Mantiene la regla de jerarquía (no eliminar a alguien con nivel mayor) y "no eliminar a uno mismo".
- Aplica a todos los roles, incluido superadmin. Si hay que romperlo → desde la BD.

### 1.5 Papelera solo para `usuarios.gestionar_papelera`
- Nuevo permiso `usuarios.gestionar_papelera` añadido al seeder y a la **lista de exclusiones del rol `administrador`** (junto a `usuarios.crear_superadmin`, `usuarios.eliminar`, `albaranes.invalidar_firma`, `clientes.gestionar_papelera`). Resultado: por defecto solo superadmin lo tiene, pero técnicamente se puede asignar a cualquier rol desde la pantalla de Roles.
- `UserPolicy::restore()` exige este permiso (en lugar de `usuarios.eliminar` + jerarquía).
- En el listado:
  - Quitada la opción **"En papelera"** del filtro Estado (ya no es un filtro normal).
  - Nuevo **checkbox "Papelera"** en el toolbar, visible solo si `auth()->user()->can('usuarios.gestionar_papelera')` (computed `puedeVerPapelera`).
  - Cuando se marca: el listado pasa a `onlyTrashed()` y aparece un **banner amber** indicando el modo + botón "Salir".
- Restaurar solo se ve y funciona para quien tenga el permiso.

### 1.6 Eliminar con modal informativo (no error 403)
- Botón Eliminar gated con `@can('usuarios.eliminar')` (no con `@can('delete', $usuario)`). Se ve siempre que tengas permiso; el bloqueo por dependencias se gestiona al pulsar.
- `confirmarEliminar()` usa `Gate::inspect`. Si denegado → muestra modal informativo con el mensaje del Policy + botón "Entendido". Si permitido → modal de confirmación habitual.
- Mensajes flash neutros: *"Usuario «pepe» eliminado correctamente."* (sin mencionar papelera).

### 1.7 Importar Excel — `/usuarios/importar`
Pantalla full-page con mapeo de columnas, all-or-nothing.

- **Obligatorios** en el Excel: `username`, `password`, `nombre`, `tipo` (interno/externo) y `rol`.
- Si tipo=externo → también obligatorio el **código de cliente** (entero único en `clientes.codigo_cliente`).
- **Rol**: match **case-insensitive** contra el catálogo `roles` (incluidos custom). Si no existe → error en esa fila.
- **Permiso por fila**: si el actor no tiene permiso para crear ese rol específico (`UserPolicy::puedeAsignarRol`), error en esa fila. All-or-nothing → bloquea toda la importación.
- **Acceso**: opcional. Si se mapea, se valida; si no, hereda del rol.
- **Password**: se hashea con `Hash::make()` antes de insertar.
- Email/DNI/CIF/teléfono/nº empleado **no** se validan por unicidad (pueden repetirse, igual que en el alta manual).
- Límites: 15 MB de archivo, 5.000 filas por importación.
- Reglas leídas desde `UserFields::getValidationRules()` (no se duplican).

### 1.8 Exportar Excel + PDF (vertical y horizontal)
- `UsuariosExport.php` (maatwebsite/excel) + `ExportarExcelController` + `ExportarPdfController` + plantilla `pdf/usuarios/lista.blade.php`.
- Respeta filtros (búsqueda, estado, tipo, rol, empresa) + orden + **jerarquía** (no exporta usuarios con nivel mayor al actor).
- 13 columnas: ID, Usuario, Nombre, Apellidos, Email, DNI, CIF, Teléfono, Nº empleado, Tipo, Rol, Empresa, Activo.
- **`password` NUNCA se exporta** (obviamente).
- PDF con logo de empresa, colores de marca, footer paginado, fuentes 6.5pt (V) / 7.5pt (H).
- mPDF v8 directo (sin wrapper abandonado).
- Modal Alpine "Descargando archivo" igual que en Clientes.

### 1.9 Contador `(N)` junto al título
- Total real de usuarios activos+inactivos (excluye papelera). No cambia entre modos.
- `totalPapelera` se usa solo en el badge del checkbox y el banner.

---

## 2. Archivos nuevos / modificados

| Archivo | Estado | Descripción |
|---|---|---|
| `database/migrations/2026_05_20_120000_add_numero_empleado_to_users.php` | **Nuevo** | Añade `numero_empleado VARCHAR(50) NULL` |
| `app/Support/UserFields.php` | **Nuevo** | Fuente única de reglas + uniqueFields (espejo de ClienteFields) |
| `app/Models/User.php` | Modificado | Añadido `numero_empleado` a fillable |
| `app/Livewire/Forms/UserForm.php` | Refactorizado | Lee reglas de UserFields, unique dinámico, nuevo campo numero_empleado |
| `app/Policies/UserPolicy.php` | Modificado | `delete()` con bloqueo deps; `restore()` con permiso gestionar_papelera |
| `database/seeders/RolesAndPermissionsSeeder.php` | Modificado | 4 permisos nuevos (exportar/importar/imprimir/gestionar_papelera) + exclusión admin |
| `app/Livewire/Usuarios/Index.php` | Modificado | verPapelera, puedeVerPapelera, totalUsuarios, totalPapelera, bloqueadoEliminarMensaje, cerrarBloqueo, exportarExcel, exportarPdf, render adaptado |
| `app/Livewire/Usuarios/Importar.php` | **Nuevo** | Componente full-page de importación |
| `resources/views/livewire/usuarios/importar.blade.php` | **Nueva** | Vista del flujo de importación |
| `app/Exports/UsuariosExport.php` | **Nuevo** | Export Maatwebsite con jerarquía |
| `app/Http/Controllers/Usuarios/ExportarExcelController.php` | **Nuevo** | Descarga Excel |
| `app/Http/Controllers/Usuarios/ExportarPdfController.php` | **Nuevo** | Descarga PDF |
| `resources/views/pdf/usuarios/lista.blade.php` | **Nueva** | Plantilla PDF |
| `routes/web.php` | Modificado | 3 rutas nuevas: `usuarios.importar`, `usuarios.exportar.excel`, `usuarios.exportar.pdf` |
| `resources/views/livewire/usuarios/index.blade.php` | Modificado | Page-header badge, papelera checkbox, banner, exports activos, importar activo, modal informativo, modal descarga Alpine, campo nº empleado en modal alta/editar |

---

## 3. Decisiones tomadas y POR QUÉ

| Decisión | Por qué |
|---|---|
| **`id` como código visible** (no creamos `codigo_usuario`) | Los usuarios son entidades internas para autenticar; no tienen vida fuera de la app (a diferencia de Clientes que sí tienen significado fiscal). El `id` autoincremental ya cumple: único, inmutable, no-importable. Cero coste |
| **`numero_empleado` como texto libre, no único, opcional** | Es información HR-side, cada empresa lo nombra a su manera ("EMP-001", "5234"). Validarlo o exigirlo sería forzar políticas ajenas |
| **Solo `username` único** | Es la clave del login. Email/DNI/CIF pueden repetirse (como en alta) — coherente |
| **Bloqueo de eliminación por proyectos + albaranes** | Decisión explícita del usuario: *"no pueden borrar cliente si albarán y proyecto lo están usando"*. Aplicado igual a Usuarios. Las firmas no bloquean porque tienen snapshot_data por diseño |
| **Bloqueo aplica también a superadmin** | Es una regla de integridad de datos, no de permisos. Si hace falta romperla, desde la BD |
| **Importar exige las mismas obligatorias que el alta** | El usuario lo pidió expresamente: *"en principio es la misma regla que cuando se crea un usuario"*. Cero ventaja para el atajo del Excel |
| **Empresa identificada por `codigo_cliente`** (no por nombre) | Cliente.nombre no es único. Usar el código entero elimina toda ambigüedad |
| **Rol case-insensitive contra el catálogo** | Es Excel manual; tolerar mayúsculas/minúsculas es UX. Pero el match es exacto (sin fuzzy) para evitar errores silenciosos |
| **Permiso por rol al importar** | Si el admin no puede crear superadmins desde el alta, tampoco desde el Excel. Coherente con la jerarquía |
| **All-or-nothing también para permisos de rol** | Coherente con el resto: una fila con un rol no permitido bloquea TODA la importación |
| **Acepta roles personalizados** | Si han creado roles custom desde la pantalla de Roles, deben poder importarlos |
| **Acceso opcional, hereda del rol** | Si no se mapea, se toma el `acceso` del rol asignado. Reduce columnas innecesarias en el Excel |
| **Passwords hasheadas en el import** | Obvio. El Excel trae texto plano (el admin lo escribe), el almacenamiento es hash |
| **Permiso `usuarios.gestionar_papelera` como permiso (no rol)** | Mismo patrón que clientes: por defecto solo superadmin, pero gestionable desde la pantalla de Roles sin tocar código |
| **Botón Eliminar gated por `usuarios.eliminar`** (no por `delete`) | Si gated por Policy `delete`, el botón se oculta cuando hay deps → el admin no sabe por qué. Mostrarlo y bloquear al pulsar es mejor UX |
| **password NUNCA se exporta** | Obvio: es un hash; exportarlo no aporta nada y es un riesgo de seguridad |
| **Contador `(N)` solo activos+inactivos** | Decisión expresa del usuario; consistente con Clientes |
| **Mensajes flash neutros** ("eliminado", no "enviado a papelera") | El admin no debe saber del modelo interno de papelera; para él es eliminar y punto |

---

## 4. Estado de calidad

### Verificado ✅
- `php -l` OK en los 11 archivos PHP nuevos/modificados.
- **Pint passed** en todos.
- `view:cache` compila sin errores.
- `route:list --name=usuarios` → 4 rutas registradas correctamente.
- Permisos en BD: 4 nuevos creados, **administrador correctamente excluido** de `usuarios.gestionar_papelera`.
- Autoload de las 5 clases nuevas confirmado.
- Migración aplicada (`numero_empleado` ya está en BD).
- Cache de Spatie limpiada (`permission:cache-reset`).

### No verificado ⚠️
- **Recorrido real en navegador**: subir un xlsx, mapear, importar (caso éxito + casos de error), exportar Excel y los dos PDFs, intentar eliminar un usuario con/sin dependencias, activar el modo papelera siendo superadmin y restaurar.

---

## 5. Pendiente / siguientes pasos

### Inmediato (tú lo controlas)
- [ ] **Prueba real en navegador**: ver punto 4 — round-trip completo de import/export + bloqueos + papelera.
- [ ] Probar con archivos límite (cerca de 15 MB, cerca de 5.000 filas).
- [ ] Probar la jerarquía: un administrador no debe ver superadmins en el listado ni poder importarlos.

### Próximos módulos (mismo patrón)
- [ ] **Proyectos**: import/export + bloqueo al eliminar si hay **albaranes asociados** + papelera con permiso `proyectos.gestionar_papelera`.
- [ ] **Materiales**: import/export + bloqueo al eliminar si hay **líneas de albarán** que lo referencian + papelera con permiso `materiales.gestionar_papelera`.
- [ ] **Conceptos**: hoy tiene botones import/export en pantalla pero **sin permisos en seeder** ni implementación. Decidir si lo abordamos o si lo desactivamos hasta que se necesite.
- [ ] **Albaranes**: export Excel + PDF (Imprimir) ya pendiente desde Fase 5; replica del patrón.

### Mejoras opcionales (no urgentes)
- [ ] En el import: ofrecer descarga de una **plantilla Excel** con las columnas esperadas y un par de filas de ejemplo. Reduce errores de los usuarios.
- [ ] En el import: si un email/DNI/CIF ya existe en BD, mostrar **aviso no-bloqueante** en el reporte final (como hace el alta manual con `buscarDuplicados()`), aunque no impida el import.
- [ ] En el export: opción de incluir/excluir columnas (selección manual antes de descargar).

### Higiene de stack pendiente
- [ ] Cuando se acerque despliegue a producción: instalar `spatie/laravel-backup`, `laravel/pulse`, `spatie/laravel-health`, `opcodesio/log-viewer` (todos triviales).
- [ ] `chart.js` cuando se construya el "Resumen mensual" (Fase 5 móvil).

---

## 6. Memoria persistente

Guardado para futuras sesiones: [`usuarios-import-export-papelera`](memoria — `~/.claude/projects/.../memory/usuarios-import-export-papelera.md`).

Cubre el patrón completo: campo nuevo, fuente única de validación, bloqueo por deps, papelera con permiso, mensajes neutros, importar con rol/empresa/permiso por fila, exportar con jerarquía.
