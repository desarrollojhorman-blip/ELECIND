# Resumen de Sesión — 2026-05-20 (mañana) · Stack: Telescope + Flatpickr + Gestión de errores

Fecha: 2026-05-20 ~09:00
Estado: Telescope (dev) y Flatpickr **instalados y funcionales**; nueva guía de gestión de errores escrita.
Ámbito: Web (Telescope local) + componentes de UI (date-input). Decisiones de stack.

---

## 1. Contexto: por qué entró este tema

Revisión del **stack técnico Entreredes 2026** contra lo que realmente usa ELECIND.
Detectado:

- Lo que tenemos coincide razonablemente con la guía (`permission`, `activitylog`, `medialibrary`, `excel`, `alpine`, `pint`, `larastan`).
- Divergencias justificadas: **Livewire 4** (la guía marcaba 3, Livewire 4 es la versión nueva), **Fortify** en vez de Sanctum (no hay API), **mPDF directo** (el wrapper `carlos-meneses/laravel-mpdf` estaba abandonado, lo cambiamos ayer).
- Faltaban librerías de la guía. Tras analizarlas una a una, **decisión:**

| Librería | Decisión | Motivo |
|---|---|---|
| **`laravel/telescope`** | ✅ **Instalar ahora** | Quedan correcciones; permite depurar Livewire/SQL/excepciones con UI en vez de `dd()` y logs en bruto |
| **`flatpickr`** | ✅ **Instalar ahora** | Vendrá bien para fechas/horas (móvil + web) con UX consistente; el `<input type="date">` nativo varía entre iOS/Android/desktop |
| `opcodesio/log-viewer` | 🟡 Después | Útil sobre todo en producción; ahora `laravel/pail` cubre |
| `chart.js` | 🟡 Después | Para el "Resumen mensual" (Fase 5 móvil), aún no toca |
| `laravel/sanctum` | ❌ No, hasta Fase 6 | No hay API/SPA; Fortify cubre el login web |
| `spatie/laravel-backup` | ❌ No, hasta producción | Hoy estamos en local; nada que respaldar todavía |
| `spatie/laravel-pulse` / `laravel-health` | ❌ No, hasta producción | Métricas/monitorización solo tienen sentido con tráfico real |
| `spatie/laravel-responsecache` | ❌ No, nunca aquí | App autenticada; cache de respuestas no aporta |
| `cviebrock/eloquent-sluggable` | ❌ No | URLs internas con ID numérico; los slugs son para webs públicas |
| `intervention/image` | ❌ No (por ahora) | No procesamos imágenes (firmas y logo se guardan tal cual) |
| `laravel/dusk` | ❌ No (por ahora) | PHPUnit es suficiente; Dusk se valorará si hace falta browser-testing |
| `spatie/laravel-data` | ❌ No (por ahora) | API mínima; cuando llegue Fase 6, se valora |
| `sortablejs` | ❌ No (por ahora) | No hay UI con drag-and-drop |
| `laravel/telescope` también descartado en producción | (alineado con instalación dev-only) |

---

## 2. Qué se ha instalado

### 2.1 Laravel Telescope (dev-only)

Panel web local para depurar todo lo que pasa por el servidor: cada
petición Livewire, cada query SQL, cada excepción, cada email, cada job…

**Instalación**:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate          # crea telescope_entries, telescope_monitoring
```

**Acceso**: `http://localhost/.../public/telescope` (estando logueado en local).

**Setup dev-only seguro** (no rompe `composer install --no-dev` en producción):
- `composer.json` → `extra.laravel.dont-discover: ["laravel/telescope"]`
- `bootstrap/providers.php` → **NO** lista `TelescopeServiceProvider`
- `AppServiceProvider::register()` lo registra **condicionalmente**:
  ```php
  if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
      $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
      $this->app->register(TelescopeServiceProvider::class);
  }
  ```

### 2.2 Flatpickr (selector fechas/horas)

Reemplazo cross-browser/cross-device del `<input type="date">` nativo.

**Instalación**:
```bash
npm install flatpickr
npm run build
```

**Setup global** en `resources/js/app.js`:
```js
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import 'flatpickr/dist/flatpickr.min.css';
flatpickr.localize(Spanish);
window.flatpickr = flatpickr;
```

**Componente Blade reutilizable** `<x-ui.date-input>` (`resources/views/components/ui/date-input.blade.php`):
- Props: `wireModel`, `live`, `enableTime`, `mode` (single/multiple/range), `placeholder`, `name`, `minDate`, `maxDate`.
- Estructura con `<div wire:ignore>` envolviendo el `<input>` → Livewire no morphea el DOM que mete flatpickr.
- `altInput: true`: el usuario ve `19/05/2026` (formato español) mientras el input real lleva `2026-05-19` (ISO, lo que recibe Livewire).
- Locale español global → todos los flatpickr de la app salen traducidos.

**Uso típico**:
```blade
<x-ui.date-input wireModel="form.fecha" />
<x-ui.date-input wireModel="form.inicio" enableTime />
<x-ui.date-input wireModel="form.rango" mode="range" />
<x-ui.date-input wireModel="filtroFecha" live />
```

---

## 3. Documentación creada

### 3.1 `docs/errores/gestion_de_errores.md` (nuevo)

Guía completa de cómo gestionamos errores **desde ahora** con Telescope incorporado.
Cubre:

- **Regla de oro**: diagnosticar antes de arreglar.
- Herramientas y cuándo usar cada una (Telescope · pail · Log:: · DevTools · Ignition).
- Flujo estándar (reproducir → Telescope → causa raíz → fix → confirmar).
- Casos típicos (Livewire que no responde, 500, lento, email no llega, select que pierde valor, wire:navigate, tests rotos).
- Patrones de manejo que usamos en la app (all-or-nothing en imports, `addError` por campo, flash global único, mensajes en español, soft delete).
- Anti-patrones a evitar.
- **Tabla de incidencias reales documentadas** (BOM, doble Alpine, morph bleed, etc.) con cómo se cazarían hoy con Telescope.
- Checklist rápido + apéndice de comandos útiles.

---

## 4. Archivos nuevos / modificados

| Archivo | Estado | Descripción |
|---|---|---|
| `composer.json` / `composer.lock` | Modificado | `laravel/telescope` en `require-dev` + `dont-discover` |
| `app/Providers/TelescopeServiceProvider.php` | **Nuevo** (`telescope:install`) | Provider publicado por Telescope (filtros, gate) |
| `config/telescope.php` | **Nuevo** (`telescope:install`) | Configuración del paquete |
| `database/migrations/2026_05_20_065639_create_telescope_entries_table.php` | **Nuevo** | Tablas `telescope_entries` y `telescope_monitoring` |
| `app/Providers/AppServiceProvider.php` | Modificado | Registro condicional dev-only de Telescope |
| `bootstrap/providers.php` | Modificado | Eliminado `TelescopeServiceProvider` (lo registra AppServiceProvider) |
| `package.json` / `package-lock.json` | Modificado | `flatpickr` añadido |
| `resources/js/app.js` | Modificado | Import de flatpickr + locale es + CSS, expuesto en `window.flatpickr` |
| `public/build/*` | Regenerado | `npm run build` con flatpickr incluido (1.33s) |
| `resources/views/components/ui/date-input.blade.php` | **Nuevo** | Componente `<x-ui.date-input>` reutilizable |
| `docs/errores/gestion_de_errores.md` | **Nuevo** | Guía oficial de gestión de errores |

---

## 5. Decisiones tomadas y POR QUÉ

| Decisión | Por qué |
|---|---|
| **Instalar Telescope ahora y no después** | Quedan correcciones por delante; bugs como el BOM o el doble Alpine se cazan en minutos con Telescope en vez de horas a ciegas |
| **Telescope como `--dev`** | No queremos su overhead, sus tablas ni su acceso en producción. En desarrollo basta y sobra |
| **Registro condicional en `AppServiceProvider`** (en lugar del `bootstrap/providers.php` por defecto) | `composer install --no-dev` en producción no instala Telescope → el provider del paquete no existe → si estuviera listado en `providers.php` rompería el arranque. La doble guarda `environment('local') && class_exists(...)` lo evita |
| **`dont-discover: laravel/telescope`** | Evitar que el package-discovery automático lo registre por su cuenta — fuente única: lo registra `AppServiceProvider` |
| **Flatpickr como librería global con `window.flatpickr`** | Permite usarlo desde Alpine inline sin tener que importarlo en cada componente. Una sola vez en `app.js` |
| **Locale español aplicado globalmente** | Todos los flatpickr de la app salen traducidos sin tener que pasarlo en cada uso |
| **`altInput: true` con dos formatos** | El usuario ve `dd/mm/yyyy` (familiar en España); Livewire/BD reciben `Y-m-d` (ISO, sin ambigüedad) |
| **Componente `<x-ui.date-input>` reutilizable** | Centraliza la configuración (clases, wire:ignore, opciones) — el desarrollador no tiene que recordar el patrón cada vez |
| **`<div wire:ignore>` envolviendo el input** | Sin esto, Livewire al morphear el DOM borraría las modificaciones de flatpickr y se rompería el calendario tras cada re-render |
| **Escribir la guía de errores AHORA**, no después | Tener Telescope sin documentación de cómo usarlo es desperdiciarlo. Mejor capturar la doctrina mientras está fresca y atarla a las herramientas reales |
| **Diferir log-viewer / Pulse / Health / Backup** | Son para producción, no estamos en producción. Pail + Telescope cubren depuración local |
| **Diferir chart.js** | Su sitio es el "Resumen mensual" (móvil) de Fase 5 — aún no construido |
| **No tocar Sanctum / Sluggable / Intervention / ResponseCache** | No aplican al proyecto en su estado y propósito actuales |

---

## 6. Estado de calidad

- `composer require` → OK, 1 paquete (Telescope) + deps actualizadas
- `php artisan telescope:install` → OK (config, provider, migración publicados)
- `php artisan migrate` → OK (tablas telescope creadas en MySQL `elecind`)
- `npm install flatpickr` → OK (1 paquete, 0 vulnerabilidades)
- `npm run build` → OK (67 módulos, 1.33s, CSS de flatpickr empaquetado)
- `composer dump-autoload` → OK (9.840 clases)
- `php artisan route:list --name=telescope` → OK (`telescope/{view?}`)
- `php artisan view:cache` → OK (incluye `date-input.blade.php`)
- `pint` → passed en archivos modificados

**Verificación en navegador**: pendiente del usuario (entrar a `/telescope` y probar `<x-ui.date-input>` en algún form real).

---

## 7. Pendiente / siguientes pasos

- [ ] Prueba real en navegador: abrir `/telescope`, importar un xlsx mirando la pestaña Livewire en paralelo, ver el detalle.
- [ ] Enchufar `<x-ui.date-input>` en algún form existente como ejemplo vivo (p.ej. fecha de albarán o un filtro de rango en listados).
- [ ] Cuando entremos en pantallas con muchas fechas (ausencias, resumen mensual) usar el componente.
- [ ] Replicar exportación a otros módulos (Proyectos / Materiales / Albaranes / Conceptos / Usuarios).
- [ ] Implementar "Imprimir lista" de Clientes (único "Pronto" restante en Clientes).
- [ ] Resolver incoherencia de permisos en `conceptos`/`usuarios` (botones import/export sin permiso en seeder).
- [ ] Cuando se acerque el despliegue: revisar `laravel/pulse`, `laravel/health`, `spatie/laravel-backup`, `opcodesio/log-viewer`.

---

## 8. Memoria persistente actualizada

Guardados como memoria para futuras sesiones:

- **`telescope-dev-only`** — instalación dev-only segura (`dont-discover` + registro condicional).
- **`flatpickr-date-input`** — flatpickr global + componente `<x-ui.date-input>` con `wire:ignore`.
