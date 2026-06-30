# 📋 CRUD Web completo — Clientes, Proyectos y Albaranes

**Fecha:** 15/05/2026  
**Hora:** 05:00  
**Sesión:** Implementación de páginas Ver/Editar/Crear para Clientes, Proyectos y Albaranes en la interfaz web de administración.  
**Estado global:** Fase 2 ~70 % · Listados + CRUD web de Albaranes completados · Firma pendiente.

---

## Resumen ejecutivo

Esta sesión convierte las pantallas web de administración de **modo modal** a **modo página completa** (patrón Ver + Editar), aplica un sistema de campos readonly consistente en toda la app, y cierra la Iter. 5 del CRUD web de albaranes (listado + ver + crear/editar).

---

## 1. Patrón Ver/Editar establecido (Clientes y Proyectos)

### Problema
Las pantallas de Clientes y Proyectos usaban modales para crear/editar. Esto generaba:
- Formularios pequeños y difíciles de escalar.
- Estado caché de Livewire en `wire:navigate` que dejaba formularios vacíos.
- Sin página de detalle ("Ver") dedicada.

### Solución aplicada
Se migra a un patrón de tres pantallas por entidad:

| Pantalla | URL | Botones cabecera |
|---|---|---|
| **Index** | `/clientes` | Nuevo → crear |
| **Crear** | `/clientes/crear` | Cancelar (→ listado) + Guardar (→ editar del nuevo) |
| **Editar** | `/clientes/{id}/editar` | ← Clientes + Eliminar + Guardar (se queda) |
| **Ver** | `/clientes/{id}` | ← Clientes + Editar + Eliminar |

**Regla de navegación:**
- `Guardar` en crear → `redirectRoute()` sin `navigate: true` (hard redirect para forzar `mount()` fresco).
- `Editar` desde index o ver → `wire:navigate.fresh` (fuerza remount del componente).
- `Eliminar` → redirect al listado con `navigate: true`.

### Fix `wire:navigate` / formulario vacío
Al navegar con `wire:navigate` a un componente ya cargado, Livewire sirve la versión cacheada sin llamar a `mount()`. Fix: usar `wire:navigate.fresh` en todos los botones de edición.

---

## 2. Campos readonly — componentes UI

### Problema
La página Ver necesitaba los mismos inputs que Editar pero sin edición. La pseudo-clase Tailwind `read-only:` no se compilaba fiablemente en JIT.

### Solución
Detección en PHP dentro de cada componente de UI:

```php
// resources/views/components/ui/input.blade.php
@php $readonly = $attributes->has('readonly'); @endphp
<input {{ $attributes->class([
    'border-slate-300 bg-white focus:border-primary-500' => !$readonly,
    'border-slate-200 bg-slate-50 text-slate-500 cursor-default' => $readonly,
]) }}>
```

Lo mismo en `textarea.blade.php` con `resize-none` añadido cuando readonly.

Resultado: cualquier componente de la app acepta `readonly` y aplica el estilo gris automáticamente.

---

## 3. Clientes — cambios concretos

### `app/Livewire/Clientes/Editar.php`
- `guardar()` redirige a `clientes.editar` sin `navigate: true`.
- Añadidos `confirmarEliminarId`, `confirmarEliminar()`, `cancelarEliminar()`, `eliminar()`.
- `eliminar()` redirige a `clientes.index` con `navigate: true`.

### `resources/views/livewire/clientes/editar.blade.php`
- Header condicional: "✕ Cancelar" (crear) vs "← Clientes + Eliminar" (editar).
- Acordeones de proyectos y usuarios cerrados por defecto.
- Modal de confirmación de borrado al final.

### `resources/views/livewire/clientes/ver.blade.php` *(reescrito)*
- Misma estructura de grid que editar, todos los inputs con `readonly`.
- Checkbox con `disabled`.
- Header: "← Clientes" + "Editar" (info, `.fresh`) + "Eliminar" (@can).
- 2 acordeones (proyectos, usuarios) cerrados por defecto.
- Modal de confirmación de borrado.

### `resources/views/livewire/clientes/index.blade.php`
- Botón "Nuevo" → `as="a" href="{{ route('clientes.crear') }}" wire:navigate`.
- Ver → `wire:navigate`, Editar → `wire:navigate.fresh`.

---

## 4. Proyectos — migración modal → páginas

### Archivos nuevos
- `app/Livewire/Proyectos/Editar.php` — componente completo con:
  - Acordeones: Trabajadores, Responsables, Conceptos, Materiales (add/remove con searchable-select).
  - Sub-modal para crear tipo de proyecto al vuelo.
  - Modal eliminar con redirect al listado.
- `app/Livewire/Proyectos/Ver.php` — solo lectura con computed para las 4 relaciones.
- `resources/views/livewire/proyectos/editar.blade.php` — 4 acordeones con encabezado de dos líneas (título + subtítulo + badge contador).
- `resources/views/livewire/proyectos/ver.blade.php` — misma estructura con inputs readonly.

### `app/Livewire/Proyectos/Index.php`
- Eliminados todos los props y métodos de modal.
- Conservados: filtros, orden, búsqueda, eliminar/restaurar, computeds de listas disponibles.

### `resources/views/livewire/proyectos/index.blade.php`
- "Nuevo" → `wire:navigate` a `/proyectos/crear`.
- Ver → `wire:navigate`, Editar → `wire:navigate.fresh`.
- Eliminado el modal principal y sub-modal de tipos.

### `routes/web.php`
- Orden correcto: `/proyectos/crear` y `/proyectos/grupos` **antes** de `{proyecto}`.

---

## 5. Albaranes web — Iter. 5 completada

### 5.1 Index (`/albaranes`)

**Componente:** `app/Livewire/Albaranes/Index.php`  
**Blade:** `resources/views/livewire/albaranes/index.blade.php`

Funcionalidades:
- Búsqueda por número, nombre de cliente o nombre de proyecto.
- Filtros: estado (incluye "En papelera" para soft delete), cliente, tipo de jornada, fecha desde/hasta.
- Chips de filtros activos con opción de quitarlos individualmente.
- Ordenación: numero, fecha, estado, tipo_hora.
- Tabla 8 columnas: Nº Albarán (con creador debajo) | Fecha | Cliente | Proyecto + código | Concepto | Jornada (badge con tono festivo/noche) | Estado (badge) | Acciones.
- Soft delete: botón Eliminar → papelera; filtro "En papelera" + botón Restaurar.
- Botón **"Nuevo albarán"** (visible según permiso `albaranes.crear_web`).
- Botón **Ver** (ojo) habilitado → `/albaranes/{id}`.
- Botón **Editar** (lápiz, visible según `@can('update', $albaran)`) → `/albaranes/{id}/editar` con `.fresh`.
- Sidebar de navegación: ruta `albaranes.index` + permiso `albaranes.ver_todos` configurados.

### 5.2 Ver (`/albaranes/{id}`)

**Componente:** `app/Livewire/Albaranes/Ver.php`  
**Blade:** `resources/views/livewire/albaranes/ver.blade.php`

Secciones:
- **Cabecera readonly**: Nº Albarán, Estado (badge), Proyecto, Cliente, Fecha, Tipo jornada, Concepto, Responsable, Creado por, Observaciones.
- **Personal**: tabla con Trabajador | Horas | H. extra | Total calculado.
- **Materiales**: tabla con Material | Cantidad | Unidad.
- **Firmas**: bloque con `<img>` de cada firma + tipo (solo si hay firmas).
- Header: "← Albaranes" + "Editar" (@can update) + "Eliminar" (@can delete).
- Modal de confirmación de borrado.

### 5.3 Editar/Crear (`/albaranes/{id}/editar` y `/albaranes/crear`)

**Componente:** `app/Livewire/Albaranes/Editar.php`  
**Blade:** `resources/views/livewire/albaranes/editar.blade.php`

Secciones:
1. **Cabecera** (form card): Proyecto (searchable-select, auto-rellena cliente), Fecha, Tipo de jornada, Concepto (se filtra por proyecto), Responsable, Observaciones.
2. **Mis horas**: Horas normales + Horas extra.
3. **Compañeros** (tarjeta con botón "Añadir compañero"): tabla inline con searchable-select del trabajador + horas + h. extra + botón quitar por fila.
4. **Materiales** (tarjeta con botón "Añadir material"): tabla inline con searchable-select del material (filtra por proyecto si hay uno) + cantidad + botón quitar por fila.

Comportamiento:
- Al cambiar proyecto → `updatedFormProyectoId()` → `sincronizarClienteDesdeProyecto()` (auto-rellena cliente y responsable sugerido).
- `guardar()` llama a `AlbaranForm::save()` que corre en transacción (reasigna lineasPersonal, lineasMaterial con ajuste de stock vía Observer).
- Redirige a `albaranes.editar` del albarán guardado (hard redirect, sin `navigate:true`).
- Header: "✕ Cancelar" (crear) vs "← Albaranes + Eliminar" (editar).

### 5.4 Rutas añadidas

```php
Route::get('/albaranes/crear', AlbaranesEditar::class)
    ->middleware('can:albaranes.crear_web')
    ->name('albaranes.crear');

Route::get('/albaranes/{albaran}', AlbaranesVer::class)
    ->middleware('can:albaranes.ver_todos')
    ->name('albaranes.ver');

Route::get('/albaranes/{albaran}/editar', AlbaranesEditar::class)
    ->middleware('can:albaranes.ver_todos')
    ->name('albaranes.editar');
```

> Nota: el middleware de editar usa `ver_todos` como puerta de entrada; la autorización real la gestiona `AlbaranPolicy::update()` dentro del componente.

---

## 6. Estado actual — qué está hecho

### Interfaz Web (admin)

| Módulo | Index | Ver | Editar/Crear |
|---|---|---|---|
| Clientes | ✅ | ✅ | ✅ |
| Proyectos | ✅ | ✅ | ✅ |
| Albaranes | ✅ | ✅ | ✅ |
| Materiales | ✅ | — | — |
| Usuarios | ✅ | — | — |
| Configuración | ✅ | — | ✅ |
| Roles | ✅ | — | — |
| Conceptos | ✅ | — | — |

### Interfaz Móvil (operario)

| Módulo | Listado | Ver | Crear/Editar |
|---|---|---|---|
| Albaranes | ✅ | ✅ | ✅ (parcial) |
| Dashboard | ✅ | — | — |

### Core / Backend

| Pieza | Estado |
|---|---|
| BD completa (5 tablas albaranes) | ✅ |
| Enums (EstadoAlbaran, TipoHora, TipoFirma) | ✅ |
| NumeracionService | ✅ |
| AlbaranLineaMaterialObserver (stock) | ✅ |
| AlbaranForm con save() transaccional | ✅ |
| AlbaranPolicy | ✅ |
| Configuración empresa + branding | ✅ |
| Firma + flujo legal | ❌ pendiente |
| Generación PDF | ❌ pendiente |

---

## 7. Pendientes inmediatos

### 🔴 Alta prioridad

1. **Albaranes móvil — Editar/Crear** (parcial: la lógica existe en `AlbaranForm`, falta el componente Livewire móvil con blade adaptado — selects nativos, UX táctil vertical).

2. **Iter. 4 — Firma + flujo legal** (la pieza central de Fase 2):
   - Canvas de firma con Alpine + almacenamiento PNG.
   - Doble firma presencial (trabajador + responsable).
   - Token email + ruta pública `/firmar/{token}` sin auth.
   - Transiciones automáticas: `borrador → pendiente_firma → firmado`.
   - Mail `AlbaranListoFirmaMail`.
   - Activity log de acciones críticas.

3. **Generación PDF** (con mPDF, usa `Branding::logoAlbaranUrl()`).

### 🟡 Media prioridad

4. **Albaranes web — cambio de estado**: botones de transición de estado (Borrador → Pendiente firma, etc.) en la página Ver.
5. **Proyectos desde Clientes**: los links `/proyectos/{{ $proyecto->id }}` en clientes/editar y clientes/ver deberían usar `route('proyectos.ver', $proyecto)`.
6. **Albaranes móvil**: completar la experiencia móvil (editar desde listado propio).

### 🟢 Baja prioridad / siguientes fases

- Fase 3 — Albarán personalizado.
- Fase 4 — Ausencias e incidencias.
- Fase 5 — Reportes y exportación.
- Familias de Material: reemplazar sub-modal de asignar materiales por select inline + "Añadir" (UX memory guardada).

---

## 8. Decisiones técnicas de esta sesión

| # | Decisión | Razón |
|---|---|---|
| 46 | **Páginas completas** en lugar de modales para Ver/Editar | Los formularios crecen (acordeones de relaciones, líneas de personal/material); un modal no escala. |
| 47 | **`wire:navigate.fresh`** en botones de editar | `wire:navigate` sin `.fresh` reutiliza el componente cacheado y salta `mount()`, dejando el formulario vacío. |
| 48 | **Hard redirect** (`redirectRoute` sin `navigate: true`) al guardar "crear" | Misma razón: el componente Editar debe montarse fresco con el nuevo ID vía route model binding. |
| 49 | **Readonly en PHP** (`$attributes->has('readonly')`) en componentes UI | La pseudo-clase Tailwind `read-only:` no siempre se compila en JIT; la detección PHP es 100 % fiable. |
| 50 | **Conceptos filtrados por proyecto** en editar albarán | Un albarán pertenece a un proyecto; solo tienen sentido los conceptos de ese proyecto (los conceptos globales solo si no hay proyecto). |
| 51 | **Materiales filtrados por proyecto** en editar albarán | Ídem que conceptos. Si no hay proyecto, se muestran todos. |
| 52 | **`AlbaranForm` reutilizado tal cual** en web | Toda la lógica de guardado, validación y ajuste de stock ya está encapsulada ahí; crear otro Form sería duplicar lógica. |

---

## 9. Hoja de ruta actualizada

```
🚧 FASE 2 ── ~70 % ────────────────────────────────────────────
   ✅ Iter. 1 — Núcleo de datos
   ✅ Iter. 2 — Infraestructura móvil
   ✅ Iter. 3 — CRUD móvil del albarán
   ✅ Refactors (tipo_hora, observaciones, branding empresa)
   ✅ Iter. 5 — CRUD web albaranes (listado + ver + crear/editar)
   ✅ CRUD web Clientes y Proyectos (páginas completas)
   ⏳ Iter. 4 — Firma + flujo legal          ← SIGUIENTE
   ⏳ Albaranes móvil editar/crear            ← en paralelo
   ⏳ Iter. 6 — Refinamiento + adjuntos
────────────────────────────────────────────────────────────────
   ⏭️ FASE 3 — Albarán personalizado
   ⏭️ FASE 4 — Ausencias e incidencias
   ⏭️ FASE 5 — Reportes y exportación
```

---

**Cierre de sesión 15/05/2026 · 05:00.** Próximo bloque: Iter. 4 (Firma) y/o editar/crear en móvil de albaranes.
