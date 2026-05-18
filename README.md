# 🏗️ ELECIND — Gestión de Albaranes, Horas y Materiales

Aplicación web + móvil para la gestión de albaranes de horas y materiales firmados digitalmente, control de stock, ausencias e incidencias.

## 🎯 Visión general

ELECIND es una aplicación personalizada para Elecind, diseñada para digitalizar la gestión de partes de trabajo, firmas, horas, materiales y ausencias del equipo.

> **Nota**: el proyecto se desarrolla en modo **single-tenant (personalizado)**. En el futuro, si interesa, se podrá evolucionar a una solución SaaS multi-tenant, pero NO es objetivo actual.

### Funcionalidades principales

- ✍️ **Albaranes firmados digitalmente** (parte normal + parte personalizado/avanzado).
- ⏱️ **Control de horas** por trabajador, tipo de hora y proyecto.
- 📦 **Gestión de stock** por lotes (proveedor + nº pedido + grupo).
- 🆘 **Ausencias e incidencias** con flujos de aprobación.
- 👥 **Roles y permisos** granulares con jerarquía por niveles.
- 📧 **Firmas por enlace con token temporal** (sin necesidad de cuenta).
- 📊 **Exportación a Excel/PDF** y reportes mensuales.

## 👤 Tipos de usuario

| Rol | Acceso | Función |
|---|---|---|
| **Superadmin** | Web + Móvil | Técnico. Visión global. Configura todo. |
| **Administrador** | Solo Web | Gestiona la operativa de la empresa. |
| **Trabajador** | Solo Móvil | Empleado interno. Crea albaranes, firma, registra horas. |
| **Responsable** | Solo Móvil / Token email | Empleado del cliente. Firma albaranes. |

> Los usuarios se distinguen por `tipo_usuario` (`interno` / `externo`). Los responsables son externos y pertenecen a una `empresa_cliente`. Ver [`docs/ARCHITECTURE_Version3.md`](./docs/ARCHITECTURE_Version3.md).

## 🛠️ Stack técnico

Stack oficial Entreredes — **100% gratis**:

- **Backend**: Laravel 12 + PHP 8.3+
- **Frontend**: Livewire 3 + Alpine.js + Blade + TailwindCSS
- **BD**: MySQL (XAMPP local / LucusHost producción)
- **Permisos**: `spatie/laravel-permission`
- **Logs**: `spatie/laravel-activitylog`
- **Media**: `spatie/laravel-medialibrary`
- **PDF**: `carlos-meneses/laravel-mpdf`
- **Excel**: `maatwebsite/excel`
- **Charts**: Chart.js
- **Fechas**: Flatpickr
- **Firma**: Canvas API nativa + Alpine.js

📄 Ver detalle completo en [`docs/STACK_Version3.md`](./docs/STACK_Version3.md).

## 📋 Documentación

| Archivo | Contenido |
|---|---|
| [`docs/STACK_Version3.md`](./docs/STACK_Version3.md) | Librerías y justificación |
| [`docs/ARCHITECTURE_Version3.md`](./docs/ARCHITECTURE_Version3.md) | Modelo de datos y arquitectura |
| [`docs/ROADMAP_Version3.md`](./docs/ROADMAP_Version3.md) | Las fases de desarrollo |
| [`docs/PERMISOS_Version3.md`](./docs/PERMISOS_Version3.md) | Roles, permisos, niveles |
| [`docs/FLUJOS_Version3.md`](./docs/FLUJOS_Version3.md) | Flujos de albarán, firma, token email |
| [`docs/CONVENCIONES_Version3.md`](./docs/CONVENCIONES_Version3.md) | Naming, ramas, commits |
| [`docs/INSTALACION_Version3.md`](./docs/INSTALACION_Version3.md) | Guía instalación local XAMPP |

## 🧭 Estado actual (Fase 2)

Avance funcional reciente en navegación y módulo de proyectos:

- Menú lateral restaurado al comportamiento clásico con iconos y submenús plegables.
- Sección Proyectos dividida en dos entradas:
	- Grupo proyectos
	- Proyectos
- Nuevo submódulo Grupo proyectos con CRUD, estado activo/desactivado, papelera (soft delete), restauración y asignación de proyectos.
- Regla de negocio aplicada: un grupo puede tener varios proyectos y cada proyecto pertenece a un solo grupo.
- Formulario de Proyectos reorganizado y simplificado:
	- Se elimina estado borrador en flujo de trabajo.
	- Soporte de Grupo nuevo al seleccionar Otro...
	- Gestión de trabajadores y responsables en edición con altas/bajas directas.
	- Acción Ver (solo lectura) disponible desde la tabla.

Resumen operativo detallado: [`docs/resumen/140526_2300_fase2_copilot.md`](./docs/resumen/140526_2300_fase2_copilot.md)

## 🧪 Incidencia resuelta: módulo Ajustes

Durante la estabilización del guardado en Ajustes se detectaron varios síntomas:

- El botón Guardar parecía refrescar la pantalla pero no persistía cambios.
- En algunos intentos el submit acababa como envío HTML nativo (query params en URL) en lugar de acción Livewire.
- Apareció un error de Livewire por múltiples elementos raíz.
- Hubo fases donde el componente quedaba mal anclado (el root reactivo no envolvía todo el contenido).

### Causa principal

La vista de Ajustes tenía problemas estructurales para Livewire (root mal detectado/anclado), mezclados con bindings complejos (Alpine + Livewire) en campos críticos, lo que hacía que el evento de guardar no fuera fiable en todos los casos.

### Solución aplicada

- Se dejó un único root válido para el componente Livewire.
- Se corrigió la estructura de la vista para que Guardar y Deshacer queden dentro del árbol reactivo.
- Se eliminó la dependencia innecesaria de submit HTML nativo para el guardado.
- Se simplificaron bindings de color a `wire:model.live` directo.
- Se añadieron trazas internas de depuración en `guardar()` con prefijo `[AJUSTES DEBUG]`.
- Se mostró un bloque de trazas en UI para diagnóstico rápido.

### Norma de trabajo a partir de ahora

Para cualquier fallo o comportamiento extraño, el diagnóstico se hará siempre primero con logs:

- Traza visual en pantalla (si aplica).
- Logs técnicos en `storage/logs/laravel.log`.
- Prefijos de contexto por módulo (ejemplo: `[AJUSTES DEBUG]`).

Solo después de confirmar el punto exacto de fallo por logs, se aplicarán cambios de código.

## 🚀 Instalación rápida

Ver [`docs/INSTALACION_Version3.md`](./docs/INSTALACION_Version3.md) para guía detallada.

```bash
git clone https://github.com/desarrollojhorman-blip/ELECIND.git
cd ELECIND
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## 🌿 Ramas

- `main` → producción estable
- `develop` → integración
- `fase-X-nombre` → cada fase de desarrollo

Ver [`docs/CONVENCIONES_Version3.md`](./docs/CONVENCIONES_Version3.md).

## 📞 Contacto

- **Owner**: desarrollojhorman-blip
- **Cliente**: Elecind
- **Hosting**: LucusHost