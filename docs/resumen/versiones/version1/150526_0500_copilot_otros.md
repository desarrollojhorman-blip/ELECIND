# 🎨 Mejoras UX / UI — Sidebar, Perfil, Móvil

**Fecha:** 15/05/2026  
**Hora:** ~05:00  
**Estado global:** Fase 2 en curso · Mejoras de experiencia de usuario fuera de las iteraciones principales

---

## Resumen de lo trabajado

Sesión enfocada en mejoras de usabilidad y pulido de la interfaz. Sin cambios en lógica de negocio ni en el dominio de albaranes.

---

## 1. Corrección de subida de imágenes (empresa)

**Problema:** Al guardar un logo en `/configuracion/empresa` saltaba error `Column not found: logo_ratio`.

**Causa:** La migración `2026_05_16_100000_add_logo_albaran_and_zoom_to_empresa` no se había ejecutado, y el enlace simbólico de storage no existía.

**Fix aplicado:**
- `php artisan migrate` — ejecutó la migración pendiente.
- `php artisan storage:link` — creó el junction entre `storage/app/public` y `public/storage`.

---

## 2. Separación de "Empresa" y "Ajustes" en Configuración

**Antes:** Una sola pantalla `/configuracion/empresa` mezclaba datos fiscales, logos, colores y parámetros de sistema.

**Ahora:** Dos ítems separados bajo el submenú "Configuración":

| Subítem | Ruta | Contenido |
|---|---|---|
| Empresa | `/configuracion/empresa` | Datos fiscales + logos (principal + albarán) + colores de marca |
| Ajustes | `/configuracion/ajustes` | Plantilla numeración albarán + caducidad token firma |

**Archivos creados/modificados:**
- `app/Livewire/Configuracion/Ajustes.php` — componente Livewire nuevo.
- `resources/views/livewire/configuracion/ajustes.blade.php` — vista nueva.
- `routes/web.php` — nueva ruta `configuracion.ajustes`.
- `resources/views/components/ui/sidebar.blade.php` — añadido ítem "Ajustes" al submenú.
- `resources/views/livewire/empresa/edit.blade.php` — reestructurada en 3 cards (datos · logos · colores); eliminados los campos de ajustes que se movieron.
- `app/Livewire/Forms/EmpresaForm.php` — eliminadas propiedades `plantilla_numeracion_albaran` y `token_caducidad_dias`; añadida `color_texto_encabezado`.

---

## 3. Campo configurable "Color texto encabezado tabla"

**Problema:** El texto de los encabezados de tabla era siempre blanco. Si alguien ponía color primario blanco, el texto desaparecía.

**Solución elegida:** Campo manual `color_texto_encabezado` (color picker) en "Empresa" → "Colores de marca". El usuario elige explícitamente el color de letra para los encabezados.

**Archivos afectados:**
- `database/migrations/2026_05_16_110000_add_color_texto_encabezado_to_empresa.php` — migración nueva (VARCHAR 7, default `#ffffff`).
- `app/Models/Empresa.php` — `color_texto_encabezado` añadido a `$fillable`.
- `app/Support/Branding.php` — nuevo helper `colorTextoEncabezado(): string`.
- `resources/css/app.css` — `--c-table-header-text: #ffffff` en `:root`; mapeado a `--color-table-header-text` en `@theme`.
- `resources/views/components/layouts/web.blade.php` — inyección dinámica de `--c-table-header-text` desde Branding.
- `resources/views/components/ui/data-table.blade.php` — `text-white` → `text-table-header-text` en `thead`.
- `resources/views/components/ui/sortable-header.blade.php` — mismo cambio de clase.

---

## 4. Menú de usuario en sidebar (web) — rediseño

**Antes:** Dropdown flotante (`position: absolute`) que se perdía fuera de pantalla al estar el usuario en la parte inferior del sidebar.

**Ahora:** Acordeón inline — las opciones se expanden hacia arriba en el flujo normal del documento, dentro del sidebar.

**Opciones disponibles:**
- Mi perfil (enlace real, antes "Pronto" deshabilitado).
- Versión móvil (solo visible si `$user->tieneAccesoMovil()`).
- Cerrar sesión.

**Archivo:** `resources/views/components/ui/sidebar.blade.php`.

---

## 5. Sidebar accordion (submenús)

**Antes:** Al abrir un submenú, los demás permanecían abiertos.

**Ahora:** Abrir un submenú cierra automáticamente cualquier otro que estuviera abierto.

**Cambio:** En el `x-data` del sidebar, `toggleExpand` pasó de añadir a un array a sustituirlo:

```js
// Antes
toggleExpand(key) { this.expanded = this.isExpanded(key) ? [...] : [...this.expanded, key]; }

// Ahora
toggleExpand(key) { this.expanded = this.isExpanded(key) ? [] : [key]; }
```

---

## 6. Página "Mi perfil" (web)

**Ruta:** `/perfil` — accesible por todos los usuarios autenticados con acceso web.

**Layout:** Dos columnas en escritorio:
- Columna izquierda: avatar con inicial + nombre + rol + badges de acceso (Web / Móvil).
- Columna derecha: tarjeta con campos — Usuario, Nombre completo, Email, Teléfono, DNI/NIF, Roles.

**Archivos creados:**
- `app/Livewire/Perfil/MiPerfil.php`
- `resources/views/livewire/perfil/mi-perfil.blade.php`
- `routes/web.php` — ruta `perfil.mi-perfil`.

---

## 7. Página "Mi perfil" (móvil)

**Ruta:** `/m/perfil` — accesible desde el menú `⋮` del header móvil.

**Layout:** Dos tarjetas verticales:
- Avatar + nombre + rol + badges de acceso.
- Datos: Usuario, Nombre, Email, Teléfono, DNI/NIF.

**Archivos creados:**
- `app/Livewire/Mobile/Perfil/MiPerfil.php`
- `resources/views/livewire/mobile/perfil/mi-perfil.blade.php`
- `routes/mobile.php` — ruta `mobile.perfil`.
- `resources/views/components/mobile/header.blade.php` — añadido enlace "Mi perfil" en el dropdown `⋮`.

---

## 8. Sidebar como drawer en móvil

**Problema:** En pantallas pequeñas (< 768 px) el sidebar desaparecía completamente sin alternativa de navegación.

**Solución:** Barra superior mínima (`md:hidden`) con solo un botón hamburguesa. Al pulsarlo, el sidebar aparece como drawer deslizante desde la izquierda con overlay oscuro.

**Comportamiento:**
- Móvil: sidebar `fixed`, empieza fuera de pantalla (`-translate-x-full`), entra al hacer clic en hamburguesa.
- Escritorio: sidebar `relative`, siempre visible y colapsable. Sin cambios respecto al comportamiento anterior.
- Cierre: botón X dentro del drawer, clic sobre el overlay oscuro, o tecla Escape.
- Comunicación entre componentes: eventos `drawer:open` / `drawer:close` via `$dispatch` de Alpine.js.

**Archivos modificados:**
- `resources/views/components/layouts/web.blade.php` — body `flex h-screen flex-col overflow-hidden`; header `md:hidden` con hamburguesa; overlay con transición.
- `resources/views/components/ui/sidebar.blade.php`:
  - `aside` pasa de `hidden md:flex` + `:class="open ? 'w-60' : 'w-16'"` a `fixed inset-y-0 left-0 z-40 w-64 md:relative md:inset-auto md:z-auto` + `:class` con lógica combinada de `drawerOpen` y `open`.
  - Añadido `drawerOpen: false` al `x-data`.
  - Todos los `x-show="open"` → `x-show="open || drawerOpen"` (y sus inversos).
  - Botón X (solo móvil) + botón Barras (solo escritorio) en la cabecera del sidebar.

---

## 9. Métricas de la sesión

```
Archivos modificados:   11
Archivos creados:        6
Migraciones nuevas:      1  (color_texto_encabezado)
Componentes Livewire:    3  (Ajustes, Perfil/MiPerfil, Mobile/Perfil/MiPerfil)
Rutas nuevas:            3  (configuracion.ajustes, perfil.mi-perfil, mobile.perfil)
Cambios en roadmap:      ninguno  (mejoras UI fuera de las iters. principales)
```

---

## 10. Estado de lo pendiente (sin cambios)

- **Fase 2 — Iter. 4:** Firma + flujo legal (Canvas, token email, PDF con mPDF). Sigue siendo el próximo objetivo.
- **Fase 2 — Iter. 5:** CRUD web del admin de albaranes.
- **Fase 2 — Iter. 6:** Adjuntos + snapshot de datos al firmar.
