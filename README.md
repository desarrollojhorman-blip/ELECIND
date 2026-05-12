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