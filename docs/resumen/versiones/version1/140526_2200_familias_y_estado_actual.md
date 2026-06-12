# 🌳 Estado consolidado — Familias de Material + situación general

**Fecha:** 14/05/2026
**Hora:** 22:00 (cierre de jornada)
**Estado global:** Fase 1 cerrada + extendida · Fase 2 al ~50 % (iter 1-3 completas) · 201 tests verde · 0 errores Larastan · Pint OK

Este documento consolida el estado real de la app **después** del último resumen oficial de Fase 2 ([`140526_1500_iter1_fase2_nucleo_datos.md`](./140526_1500_iter1_fase2_nucleo_datos.md)) y deja constancia de:

1. Lo construido en Fase 2 más allá de la iter. 1.
2. Los **refactores estructurales** hechos en mitad de Fase 2 (materiales/lotes, horas, login).
3. La nueva feature de **Familias de Material** (extensión post-cierre Fase 1).
4. Lo que **falta** ahora mismo, incluido el **rework de UX** que se ha decidido hacer en el modal "Asignar materiales" de Familias.

---

## 1. Lo que se ha construido desde la última iteración

### 🧱 Iter. 2 Fase 2 — Infraestructura móvil (✅)

- Layout `components.layouts.mobile` con header (logo + back + título dinámico).
- Rutas `/mobile/...` protegidas por middleware `ensure.mobile.access`.
- Dashboard móvil (`/mobile/dashboard`) con "Mis partes recientes" + atajo a "Nuevo parte".
- Componentes UI móviles: `<x-mobile.header>`, `<x-mobile.menu-action>`, `<x-mobile.button>`, `<x-mobile.card>`, `<x-mobile.list-item>`.
- **Login redirect por rol**: trabajadores van directos a `mobile.dashboard`, admin/superadmin a `web.dashboard`.

### 📄 Iter. 3 Fase 2 — CRUD albarán desde móvil (✅)

- Componente Livewire `Mobile\Albaranes\Crear` (alta + edición de borradores).
- Componente Livewire `Mobile\Albaranes\Ver` (visualización con líneas de personal y material).
- Selects dependientes táctiles: proyecto → conceptos / usuarios / materiales del proyecto.
- Filtro de proyectos visibles por rol: trabajadores solo ven proyectos donde están asignados (o son responsable principal); admin/superadmin con `albaranes.ver_todos` ven todos.
- Cabecera con `tipo_dia` (laborable/festivo) + observaciones generales.
- Líneas de personal con `horas` + `horas_extra` por usuario (yo + compañeros).
- Líneas de material con select directo (sin lotes, ver refactor más abajo) + cantidad + observaciones.
- Stock se descuenta automáticamente vía `AlbaranLineaMaterialObserver` al crear/editar/borrar líneas.
- "Guardar borrador" único botón (sin "Cancelar" duplicado), flecha back vuelve al dashboard si es nuevo o al "Ver parte" si es edición.

### 🔧 Refactor materiales: lotes → pedidos + material plano (✅)

Migración estructural fuerte realizada en mitad de Fase 2. **Decisión**: el modelo de `material_lotes` + `movimientos_stock` era sobreingeniería para el caso real (Elecind no necesita trazabilidad por lote ni FIFO; necesita "cuánto cable rojo me queda").

| Antes | Ahora |
|---|---|
| `materiales` (catálogo + min stock + grupo + activo) | `materiales` (catálogo plano: `descripcion`, `unidad_medida`, `stock`) |
| `material_lotes` (proveedor, n_pedido, fecha_entrada, stock_disponible…) | **eliminada** |
| `movimientos_stock` (entrada/salida/ajuste, motivo…) | **eliminada** |
| — | `numero_pedidos` (numero único, fecha, proveedor, descripción) |
| `albaran_lineas_material.material_lote_id` | `albaran_lineas_material.material_id` |
| Stock se calculaba sumando lotes | Stock vive directo en `materiales.stock` |

**Pivot `material_proyecto`**: el refactor lo eliminó accidentalmente; se recreó en una migración posterior sin la columna `cantidad_prevista` (no aportaba valor real).

### 🕒 Refactor horas en Albaran (✅)

| Antes | Ahora |
|---|---|
| Cada `albaran_lineas_personal` tenía `tipo_hora` (4 enums: `laborable_normal`, `laborable_extra`, `festivo_normal`, `festivo_extra`) y `horas` único | `Albaran` tiene `tipo_dia` (laborable/festivo) en cabecera. Cada línea personal tiene `horas` (normales) + `horas_extra` (extras) |

Más limpio: el día se decide una vez, las horas extras se separan visualmente. El enum `TipoHora` se eliminó; se introduce `TipoDia`.

### 🌳 Familias de Material (✅, con UX a retocar)

Nueva tabla `familias_material` (nombre único + descripción + soft delete) y FK opcional `materiales.familia_id` (nullOnDelete). Permite agrupar variantes del mismo artículo a través de distintos pedidos ("Cable H07V-K rojo 2.5mm²" del PED-001 + "Cable HV-K rojo 2,5" del PED-014 → ambos en familia "Cables H07V-K").

**Lo construido:**

- Migración + modelo `FamiliaMaterial` con relación `materiales(): HasMany`.
- `Material` gana relación `familia(): BelongsTo`.
- Form `FamiliaMaterialForm` con validación `nombre` único.
- Componente `Materiales\Familias\Index` (CRUD completo: alta/edición/ver/eliminar/restaurar).
- Vista `livewire/materiales/familias/index.blade.php`:
  - Listado con contador `materiales_count` por familia.
  - Modal Ver/Editar con panel **"Materiales en esta familia"** (lista de los asignados, con badge de pedido + stock + botón ✕ para quitar).
  - Modal **"Asignar materiales"** con buscador, **toggle "Mostrar también materiales con otra familia"** (por defecto solo huérfanos), tabla con checkboxes, botón "Asignar X materiales".
- Integración en `Materiales\Index`:
  - Nueva columna **Familia** ordenable.
  - Nuevo filtro **Familia** (incl. opción "— Sin familia —").
  - Buscador extendido para buscar por nombre de familia.
  - Select **Familia** opcional en el modal de Material.
- 4 permisos `materiales.familias.{ver,crear,modificar,eliminar}` (categoría "materiales", ámbito web), asignados a superadmin + administrador.
- Policy `FamiliaMaterialPolicy` registrada en `AppServiceProvider`.
- Ruta `/materiales/familias` y entrada en menú lateral bajo "Materiales".
- Seeder demo: 4 familias realistas (Cables H07V-K, Mecanismos, Tubos corrugados, Cuadros eléctricos), ~70% de materiales con familia asignada.
- 18 tests nuevos en `Familias\IndexTest` + 3 nuevos en `Materiales\IndexTest` (filtro familia, filtro sin_familia, asignar familia al crear material).

**⚠️ UX a retocar (decidido 14/05/2026 noche):**

> El modal "Asignar materiales" como **modal-dentro-de-modal** no convence. La nueva idea es:
>
> Sustituir ese modal anidado por, dentro del propio panel de "Materiales en esta familia" del modal de Familia, **un select inline + botón "Añadir"**. El select muestra los materiales sin familia. Al pulsar "Añadir", el material se asigna a la familia y aparece en la lista de arriba.
>
> No requiere modal anidado. Más rápido, menos profundidad visual. **Pendiente de implementación** (ver sección 4).

---

## 2. Métricas actuales

```
Tests:            201 passed · 643 assertions · ~71 s
Pint:             ✅ passed
Larastan:         ✅ 0 errores

Migraciones:      18 (refactores incluidos)
Modelos:          18 (Albaran + 4 familia subordinadas + Cliente + Concepto + Empresa + FamiliaMaterial + Material + NumeroPedido + Permission + Proyecto + Role + TiposProyecto + User)
Componentes Livewire web:    9 CRUDs (Clientes, Proyectos, NumeroPedidos, Materiales, Familias, Usuarios, Conceptos, Empresa, Roles)
Componentes Livewire móvil:  2 (Albaranes\Crear, Albaranes\Ver) + dashboard
Permisos:         62 (refactor: -material_lotes.* y -stock.ver_historico, +materiales.familias.* (4), +pedidos.* (4), +albaranes nuevos (4))
Policies:         10
```

---

## 3. Lo que SÍ está hecho de Fase 2 (resumen rápido)

- ✅ BD completa: 5 tablas albaranes (`albaranes`, `albaran_lineas_personal`, `albaran_lineas_material`, `albaran_firmas`, `albaran_tokens_firma`).
- ✅ Enums: `EstadoAlbaran`, `TipoDia` (sustituyó a `TipoHora`), `TipoFirma`.
- ✅ Servicios: `NumeracionService` con plantilla configurable + `lockForUpdate`.
- ✅ Observer: `AlbaranLineaMaterialObserver` ajusta `material.stock` (no `lote.stock`) tras refactor.
- ✅ Layout móvil + dashboard + componentes UI móviles.
- ✅ CRUD móvil de albarán (crear, editar, ver borradores propios).
- ✅ Demo seeder Fase 2: 5 albaranes en estados distintos (1 borrador, 1 pendiente_firma con token al responsable, 2 firmados, 1 facturado).

---

## 4. Lo que falta hacer

### 🔴 Prioridad alta — pendiente inmediato

1. **Rework UX modal "Asignar materiales" en Familias** *(decidido 14/05/2026)*:
   - Quitar el modal anidado dentro del modal de Familia.
   - En su lugar, dentro del panel **"Materiales en esta familia"** (del modal de Familia), añadir un **select inline + botón "Añadir"**:
     - Select muestra solo materiales con `familia_id IS NULL`.
     - Al pulsar "Añadir", se setea `familia_id` y el material aparece arriba en la lista de asignados.
     - Sin abrir un segundo modal.
   - Eliminar las propiedades `modalAsignarAbierto`, `buscarAsignar`, `mostrarTodosAsignar`, `materialesSeleccionados` y la computed `materialesAsignables` del componente.
   - Reemplazarlas por una propiedad simple `materialAAsignar = null` y método `agregarMaterialAFamilia()`.
   - Actualizar tests asociados (eliminar los del toggle huérfanos/todos; añadir uno nuevo del flujo "select + add").
   - Si más adelante se quiere reasignar materiales con otra familia, se hace desde el listado de Materiales (cambiando el select del modal Material), no desde Familias.

### 🟡 Fase 2 — iteraciones restantes

2. **Iter. 4 — Firma + flujo legal** (la pieza central, mayor riesgo):
   - Componente firma Canvas + Alpine, guarda PNG en storage.
   - Doble firma presencial in-situ (trabajador + responsable presente).
   - Generación de token email + ruta pública `/firmar/{token}` SIN auth.
   - Transiciones automáticas: `borrador → pendiente_firma → firmado` cuando ambos huecos están llenos.
   - Geolocalización opcional con prompt del navegador.
   - Activity log de acciones críticas.
   - `AlbaranListoFirmaMail` para el responsable.
   - Generación PDF con mPDF (plantilla configurable).

3. **Iter. 5 — CRUD web del admin de albaranes**:
   - Pantalla `/albaranes` con tabla + filtros + modal alta/edición (más campos que el móvil).
   - Cambio de firmantes asignados (`creado_por`, `responsable_id`) cuando el hueco está vacío.
   - Modal "Solicitar firma" con selector (trabajador / responsable del proyecto / email custom).
   - Gestión de tokens: reenviar, regenerar, invalidar.
   - Eliminar firmas con permiso `albaranes.invalidar_firma` + activity log.
   - Forzar transiciones de estado con permiso `albaranes.modificar_terminado`.
   - Botón "Descargar PDF" desde la tabla.

4. **Iter. 6 — Refinamiento + adjuntos**:
   - Adjuntos múltiples (PDFs externos, fotos) vía `spatie/laravel-medialibrary`.
   - Snapshot de datos al firmar (foto histórica inmutable de cliente, proyecto, líneas).
   - Tests adicionales para edge cases y flujos completos end-to-end.

### 🟢 Fases siguientes (sin tocar)

5. **Fase 3** — Albarán personalizado (selects "Otro" + creación rápida de cliente/material/concepto desde el flujo de revisión web).
6. **Fase 4** — Ausencias e incidencias.
7. **Fase 5** — Reportes y exportación (Excel + PDFs + control de horas).

---

## 5. Decisiones técnicas confirmadas en esta jornada

| # | Decisión | Razón |
|---|---|---|
| 32 | **Materiales sin lotes**: stock directo en `materiales.stock` | Sobreingeniería para el caso real Elecind. No necesitan trazabilidad por lote ni FIFO. |
| 33 | **`numero_pedidos` como agrupador de compra** | Permite saber "qué llegó en qué pedido" sin reconstruir lotes; FK `materiales.numero_pedido_id` `restrictOnDelete`. |
| 34 | **`familias_material` como agrupador opcional** | Para juntar variantes del mismo artículo entre pedidos distintos. `nullable` + `nullOnDelete`: borrar familia no borra materiales. |
| 35 | **`tipo_dia` en cabecera del albarán** *(no en cada línea)* | Una jornada se trabaja entera el mismo tipo de día. Las horas extras sí varían por persona → quedan por línea (`horas` + `horas_extra`). |
| 36 | **Las familias se dan de alta solo desde Familias** *(no "crear al vuelo" desde Material)* | Evita que un usuario operativo cree "Cable" / "Cables" / "cables" en mitad de un alta sin pensar. La gestión de familias es una decisión administrativa. |
| 37 | **Asignación de materiales a familia: select inline + botón "Añadir"** *(en lugar de modal anidado)* | El modal-dentro-de-modal genera demasiada profundidad visual. Un select del listado de huérfanos + "Añadir" cubre el 95% de los casos sin abrir nuevo modal. *(Pendiente de implementar.)* |

---

## 6. Notas operativas

- **Trabajador demo estable**: usuario `trabajador` / contraseña `password`, asignado a TODOS los proyectos demo, para poder probar el flujo móvil completo sin reconfigurar.
- **Login en `localhost/CLIENTES/ELECIND/public/login`**: si tras `migrate:fresh` aparece **419 PAGE EXPIRED**, es por la cookie de sesión vieja del navegador (sesiones en BD). Borrar cookies del sitio (F12 → Application → Cookies → localhost → Clear) o usar incógnito.
- **Error de vista compilada `rename(...): Acceso denegado (code: 5)`** en Apache después de correr tests CLI: ejecutar `php artisan view:clear`. Pasa porque CLI y Apache compilan vistas con dueños distintos en Windows.
- **PHP en `D:\xampp\php\php.exe`** (la doc original apunta a `C:\xampp` que no existe en esta máquina).
- **Composer en `C:\composer\composer.phar`**.

---

## 7. Hoja de ruta inmediata

```
🚧 FASE 2 ── 50 % ─────────────────────────────────────────
   ✅ Iter. 1 — Núcleo de datos
   ✅ Iter. 2 — Infraestructura móvil
   ✅ Iter. 3 — CRUD móvil del albarán
   🔴 PRE-Iter. 4 — Rework UX Familias (asignación inline)  ← lo más inmediato
   ⏳ Iter. 4 — Firma + flujo legal (la pieza central)
   ⏳ Iter. 5 — CRUD web del admin
   ⏳ Iter. 6 — Refinamiento + adjuntos
─────────────────────────────────────────────────────────────
   ⏭️ FASE 3 — Albarán personalizado (2-3 sem)
   ⏭️ FASE 4 — Ausencias e incidencias (2-3 sem)
   ⏭️ FASE 5 — Reportes y exportación (2 sem)
```

---

**Cierre de jornada del 14/05/2026.** Próxima tanda: rehacer UX de Familias (select inline) antes de seguir con la firma de albaranes (Iter. 4).
