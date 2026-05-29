# Avance Fase 2 — 22/05/2026 (15:00)

Materiales, Pedidos como páginas dedicadas, Conceptos con papelera/import/export.

## Resumen ejecutivo

Día centrado en cerrar el módulo de **Pedidos** (refactor modal → páginas dedicadas con grid de materiales inline) y reforzar **Conceptos** para que tenga el mismo patrón que Clientes/Usuarios (papelera con permiso dedicado, import/export, acción Ver). Al final del día, **propuesta acordada para Materiales** (sección 4): una sola tabla con un flag de módulo en Ajustes que decide la UI; flip reversible sin migrar datos. Empezaríamos por la Fase A (flag + helper + UI condicional) cuando el usuario confirme.

---

## 1. Pedidos como páginas dedicadas

Lo que era un modal con la grid de materiales dentro pasó a ser un flujo de páginas tipo "documento".

### Componentes nuevos

- `App\Livewire\Pedidos\Editar` — full-page (crear/editar). Reutiliza `NumeroPedidoForm` para la cabecera; materiales se mantienen en array `$lineas` en memoria; al pulsar Guardar se persiste todo en una sola `DB::transaction` (cabecera + crear/actualizar/eliminar materiales).
- `App\Livewire\Pedidos\Ver` — full-page readonly con los mismos dos tabs (Pedido / Consumo).
- `App\Livewire\Materiales\NumeroPedidos\Index` — refactorizado: sin modal; los botones Ver/Editar enlazan con `wire:navigate` a las páginas dedicadas.

### Rutas (orden crítico)

```
materiales.pedidos      GET /materiales/pedidos                 NumeroPedidosIndex
pedidos.crear           GET /materiales/pedidos/crear           PedidosEditar
pedidos.ver             GET /materiales/pedidos/{pedido}        PedidosVer
pedidos.editar          GET /materiales/pedidos/{pedido}/editar PedidosEditar
```

`crear` antes que `{pedido}` para que la wildcard no la capture. El parámetro `{pedido}` coincide con `mount(?NumeroPedido $pedido)` para route-model binding.

### Tabs

- **Pedido**: cabecera + grid de líneas de materiales en forma de tabla:
  - Columnas: `#` · Descripción* · Familia · Unidad* · Stock* · Coste € · Venta € · Acciones.
  - Coste/Venta gated por `materiales.gestionar_precios`.
  - En cada fila, columna **Acciones**: 🔗 *Ir al material* (`materiales.ver`, solo si la fila ya tiene id) + ✕ *Quitar del pedido*.
  - El botón "Añadir" lleva al método `agregarLinea()` — renombrado desde `añadirLinea` porque Livewire no resuelve correctamente nombres con caracteres no-ASCII en `wire:click`.
- **Consumo**: tabla con consumido (suma `cantidad` de `AlbaranLineaMaterial` con albarán no-trashed) + stock actual por material + columna *Ver* con icon-button a `materiales.ver`. Bloqueada para pedidos nuevos hasta el primer Guardar.

### Reglas de negocio

- **Bloqueo al eliminar línea**: si el material está usado en algún `albaran_lineas_material`, no se permite quitarlo de la grid → error inline. El admin debe quitarlo de los albaranes primero.
- **Flujo "documento entero o nada"**: todo se mantiene en memoria del componente Livewire hasta pulsar Guardar; `DB::transaction(...)` envuelve cabecera + delete materiales marcados + insert/update. Si algo falla → rollback.
- Tras guardar: `redirectRoute('pedidos.editar', ..., navigate: true)`.

### Sidebar

- Submenú "Nº Pedido" → **"Pedidos"**.
- Sigue existiendo `/materiales/crear` como atajo para crear material suelto asociando un pedido por código.

---

## 2. Conceptos — patrón completo

Conceptos se alineó con Clientes/Usuarios.

### Permisos nuevos (seeder + asignaciones)

- `conceptos.gestionar_papelera` — excluido del rol `administrador` (solo superadmin por defecto).
- `conceptos.exportar` — permite Excel y PDF.
- `conceptos.importar`.

### Policy

- `delete()` ya **no** bloquea por proyectos. **Solo bloquea si hay albaranes no-trashed** vinculados. El pivote `proyecto_concepto` es configuración, no historial.
- Mensaje del bloqueo sugiere expresamente **Desactivar** como alternativa para retirar el concepto sin perder histórico.
- `restore()` requiere `conceptos.gestionar_papelera`.

### Index Livewire

- `withCount(['proyectos', 'albaranes'])` en el render.
- `$soloLectura` + método `abrirVer($id)` (autoriza `view`, carga form, abre modal readonly).
- `confirmarEliminar` / `eliminar` usan `Gate::inspect` y vuelcan el mensaje a `session('error')` (lo pinta `<x-ui.flash>`); `confirmarEliminar` pasa `proyectos_count` al modal para mostrar aviso ámbar.
- `toggleActivo($id)` (gated por `update`) — desde la columna Estado.
- Filtro Estado default `''` (todos). Opciones: Todos / Activos / Inactivos.
- `$verPapelera` con URL `?papelera=1`; computeds `puedeVerPapelera` (`conceptos.gestionar_papelera`) y `totalPapelera`.
- En modo papelera el render usa `onlyTrashed()` e ignora el filtro Estado.

### Blade

- Tabla: ID · Nombre · Descripción · Proyectos · Albaranes · Estado · Acciones (`colspan=7`).
- Acciones consolidadas a **3 iconos** (Ver / Editar / Eliminar), como Clientes/Usuarios. El activar/desactivar se hace pulsando directamente en el **badge Estado** (botón con tooltip explicativo).
- Toggle **Papelera** estilo Clientes (checkbox-botón con contador) en `leftActions`, visible solo con `conceptos.gestionar_papelera`. Mientras está activo, el select Estado se deshabilita.
- Modal: título dinámico (Ver / Editar / Nuevo); inputs en `readonly`/`disabled` y botón Guardar oculto cuando es modo Ver. Las secciones plegables de Albaranes/Proyectos vinculados siguen visibles.
- Modal de confirmación: muestra aviso ámbar *"Se quitará de N proyecto/s. Mejor desactívalo desde Editar."* cuando hay proyectos vinculados pero no albaranes.

### Import/Export

Siguiendo el patrón de `clientes-importar`:

- **Exportar**: `App\Exports\ConceptosExport` (4 columnas: ID, Nombre, Descripción, Activo) + `App\Http\Controllers\Conceptos\ExportarExcelController` y `ExportarPdfController` + vista PDF `resources/views/pdf/conceptos/lista.blade.php`. Respeta filtros aplicados en el Index (incluido modo papelera, transmitido por query string).
- **Importar**: `App\Livewire\Conceptos\Importar` — upload xlsx/xls/csv (10 MB / 5000 filas máx), auto-sugerencia de mapeo por alias del encabezado, validación inline (sin helper `ConceptoFields`), unicidad case-insensitive de `nombre` contra BD y dentro del propio archivo. Regla de oro: **un solo error → no se importa nada** (`DB::transaction`).
- Dropdown del Index cableado con Importar/Exportar Excel/PDF (V/H), cada uno gated por su permiso.

---

## 3. Decisiones de UX / opinión profesional

### Sobre eliminar Conceptos

Decisión final tras debate:
- **Desactivar (`activo = false`)** es la vía para "ya no lo uso pero hay histórico". Reversible, conserva todo.
- **Eliminar → papelera**: solo cuando no hay albaranes (el pivote de proyectos no bloquea, solo avisa).
- Filtro Estado default = todos. Botón Papelera como toggle visible para quien tenga el permiso.

### Sobre la acción "Ver" de Concepto

- Decidida como **modal de solo lectura**, no página dedicada (Conceptos es entidad pequeña, no justifica refactor). Se ramifica con `$soloLectura` en el blade para añadir `readonly`/`disabled` y ocultar Guardar.

---

## 4. Propuesta acordada: Materiales como módulo activable

Discusión iniciada al final del día. **Punto de partida**: el cliente actual no necesita la gestión avanzada (pedidos, precios, stock); quiere algo simple tipo Conceptos. Pero a futuro otros clientes querrán la versión completa que ya tenemos construida. Pendiente la **luz verde explícita del usuario** para empezar la Fase A.

### Opciones consideradas

| Opción | Veredicto |
|---|---|
| Materiales viviendo dentro del proyecto (sin catálogo global) | ❌ Descartado. Repite datos, rompe reutilización, migración imposible al activar avanzado. |
| Dos tablas separadas (`materiales_simples` + `materiales`) | ❌ Descartado. Dos modelos, dos policies, dos índices, dos importadores. Migrar datos al flipar el switch sería un infierno. |
| **Una sola tabla `materiales` con flag de módulo que decide UI** | ✅ **Elegido.** Sin migraciones de datos, flip reversible, una sola FK en `albaran_lineas_material`. |

### Cómo se ve la solución

**Capa de datos** — sin migración destructiva
- Tabla `materiales` ya existe → se mantiene.
- `numero_pedido_id` se hace **nullable** (si no lo era ya).
- `precio_coste`, `precio_venta`, `stock`, `familia_id` ya son nullable.
- Cero migración de datos al flipar el flag.

**Capa de configuración** — un flag en Ajustes
- Nueva columna en `empresas` (o entrada en JSON `modulos`) → `modulo_materiales_avanzado` boolean, default `false`.
- Helper `Modulos::activo('materiales_avanzado')` → `bool`.
- Acceso simple desde Blade: `@if (\App\Support\Modulos::materialesAvanzado()) ... @endif`.

**Capa de UI** — comportamiento según el flag

| Pieza | OFF (simple) | ON (avanzado, actual) |
|---|---|---|
| Editor de Material | nombre, descripción, familia (opcional), activo | + numero_pedido_id, stock, precio_coste, precio_venta |
| Submenú "Pedidos" en sidebar | oculto | visible |
| Import/Export de Material | 3-4 columnas (nombre, descripción, familia, activo) | columnas completas con nº pedido / precios |
| Selector en albarán | lista plana del proyecto sin extras | lista con familia/pedido visibles |
| Permiso `materiales.gestionar_precios` | no aplica | sigue funcionando |

**Capa de Ajustes** — nueva sección "Módulos"
- En `/configuracion/ajustes` añadir sección "Módulos" (o ruta dedicada `/configuracion/modulos` si se prevén varios).
- Por ahora solo un toggle: **Gestión avanzada de materiales** con su descripción.
- Guardado inmediato (sin botón Guardar).

### Por qué este enfoque

| Ventaja | Por qué importa |
|---|---|
| **Flip reversible sin migrar datos** | Apagas el módulo y los materiales siguen ahí; al volver a encender, todos los precios/pedidos siguen donde estaban. |
| **Una sola FK desde albaranes** | `albaran_lineas_material.material_id` no cambia nunca. No hay que decidir a qué tabla apunta cada línea. |
| **Patrón escalable** | Mañana añadimos `modulo_facturacion`, `modulo_ausencias_avanzadas`, etc. Mismo patrón. |
| **Mental model claro para el usuario** | "Tengo materiales, y opcionalmente uso la versión profesional", no "tengo dos tipos de materiales". |
| **Compatible con snapshots** | Los Observers de albarán ya congelan los datos al firmar. Si está en modo simple y luego se apaga "precios", el snapshot del albarán antiguo conserva lo que tenía. |

### Riesgos a vigilar

1. **Materiales sin `numero_pedido_id` al activar avanzado**: si crearon 200 materiales en modo simple y luego activan avanzado, esos materiales quedan sin pedido asignado. **Decisión**: dejarlos como están (filtrables como "sin pedido asignado"); solo las altas nuevas en modo avanzado exigen pedido. No forzar migración.
2. **`materiales.gestionar_precios`** solo aplica en modo avanzado. En simple, ese permiso simplemente no se evalúa.
3. **Selectores de proyecto**: el pivote `proyecto_material` sigue siendo la fuente de verdad de qué materiales puede usar cada proyecto. El flag no toca eso.

### Plan de fases sugerido

- **Fase A** *(corta, baja riesgo)*: añadir el flag en Ajustes + helper + sección "Módulos" en configuración. UI condicional pintada pero sin tocar lógica de negocio. **Probar que el flip funciona.** Esta es la que arrancaría en cuanto confirme el usuario.
- **Fase B**: simplificar el editor de Material para modo OFF — ocultar campos avanzados, no exigir nº pedido.
- **Fase C**: simplificar import/export de Material en modo OFF — versión "como Conceptos".
- **Fase D**: ocultar el submenú "Pedidos" del sidebar cuando OFF; ajustar selectores en albarán.

### Implicaciones para la FASE 3 (import/export de materiales)

El formato del importador cambia según el modo, así que **la FASE 3 queda condicionada a tener el módulo activable en marcha** (al menos las Fases A y B). Tiene sentido completar A → B → C en bloque antes de retomar import/export como tarea separada.

---

## 5. Pendiente para próxima sesión

### Albaranes web — adaptaciones tras los cambios recientes

Ya planificado, pendiente:
1. Filtrar conceptos inactivos/trashed en selectores (mostrar los ya referenciados aunque estén inactivos para no perder datos).
2. Precios materiales en líneas gated por `materiales.gestionar_precios`.
3. Mini-link a `materiales.ver` en cada línea (como en Pedidos).
4. Indicador de "snapshot ≠ dato actual" en la vista Ver (especialmente en albaranes firmados).
5. Badge "inactivo" en conceptos/materiales desactivados del listado del albarán.

### Materiales import/export (formato F1)

Diferido a la sesión siguiente. Cada fila = un material con `nº pedido` por código. No se hace import/export de pedidos completos. **Esta tarea queda condicionada a la decisión del módulo simple/avanzado** — el formato del importador cambia según el modo.

---

## 6. Archivos tocados hoy (resumen)

### Nuevos

- `app/Livewire/Pedidos/Editar.php`
- `app/Livewire/Pedidos/Ver.php`
- `resources/views/livewire/pedidos/editar.blade.php`
- `resources/views/livewire/pedidos/ver.blade.php`
- `app/Livewire/Conceptos/Importar.php`
- `resources/views/livewire/conceptos/importar.blade.php`
- `app/Exports/ConceptosExport.php`
- `app/Http/Controllers/Conceptos/ExportarExcelController.php`
- `app/Http/Controllers/Conceptos/ExportarPdfController.php`
- `resources/views/pdf/conceptos/lista.blade.php`

### Modificados

- `app/Policies/ConceptoPolicy.php` — bloqueo solo por albaranes, `restore` gated.
- `app/Livewire/Conceptos/Index.php` — withCount albaranes, soloLectura/abrirVer, toggleActivo, verPapelera, paramsExport, exportarExcel/exportarPdf.
- `app/Livewire/Materiales/NumeroPedidos/Index.php` — sin modal; enlaces wire:navigate.
- `resources/views/livewire/conceptos/index.blade.php` — toggle papelera, badge Estado clicable, 3 acciones, modal con aviso ámbar, dropdown cableado.
- `resources/views/livewire/materiales/numero-pedidos/index.blade.php` — listado puro.
- `resources/views/components/ui/sidebar.blade.php` — "Nº Pedido" → "Pedidos".
- `routes/web.php` — rutas de Pedidos (4) y de Conceptos importar/exportar (3).
- `database/seeders/RolesAndPermissionsSeeder.php` — 3 permisos nuevos de conceptos.

---

## 7. Memorias creadas/actualizadas hoy

- `memory/pedidos-paginas-dedicadas.md` — refactor a páginas dedicadas, tabla inline, bloqueos por dependencias.
- `memory/conceptos-papelera-ver.md` — patrón completo de Conceptos (papelera + Ver + import/export + desactivar como alternativa preferida).
- Índice `MEMORY.md` actualizado.
