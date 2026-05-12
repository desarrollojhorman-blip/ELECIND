# 🏗️ ELECIND — Gestión de Albaranes, Horas y Materiales

Plataforma SaaS multi-tenant para la gestión de albaranes de horas y materiales firmados digitalmente, control de stock, ausencias e incidencias.

## 🎯 Visión general

ELECIND nace como herramienta interna para la empresa Elecind y se diseña desde el inicio como una solución **SaaS multi-tenant** lista para venderse a otras empresas del sector.

### Funcionalidades principales

- ✍️ **Albaranes firmados digitalmente** (parte normal + parte personalizado/avanzado).
- ⏱️ **Control de horas** por trabajador, tipo de hora y proyecto.
- 📦 **Gestión de stock** por lotes (proveedor + nº pedido + grupo).
- 🆘 **Ausencias e incidencias** con flujos de aprobación.
- 👥 **Roles y permisos** granulares con jerarquía por niveles.
- 📧 **Firmas por enlace con token temporal** (sin necesidad de cuenta).
- 📊 **Exportación a Excel/PDF** y reportes mensuales.
- 🌐 **Multi-tenant** con subdominios (`elecind.getradi.es`, `aluan.getradi.es`).

## 👤 Tipos de usuario

| Rol | Acceso | Función |
|---|---|---|
| **Superadmin** | Web + Móvil | Técnico. Visión global. Configura todo. |
| **Administrador** | Solo Web | Gestiona la operativa de su empresa. |
| **Trabajador** | Solo Móvil | Empleado interno. Crea albaranes, firma, registra horas. |
| **Responsable** | Solo Móvil / Token email | Empleado del cliente. Firma albaranes. |

> **Nota**: los usuarios se distinguen por `tipo_usuario` (`interno` / `externo`). Los responsables son externos y pertenecen a una `empresa_cliente`. Ver [`ARCHITECTURE.md`](./ARCHITECTURE.md).

## 🌐 Estructura de dominios

| URL | Para qué |
|---|---|
| `getradi.es` | Landing pública |
| `admin.getradi.es` | Panel SaaS central (gestión tenants) |
| `{tenant}.getradi.es` | App de cada empresa |

En local: `getradi.test`, `elecind.getradi.test`, etc.

> **Tenant** = empresa cliente que paga por usar la app. Cada tenant tiene su propia base de datos aislada.

## 🛠️ Stack técnico

Stack oficial Entreredes — **100% gratis**:

- **Backend**: Laravel 12 + PHP 8.3+
- **Frontend**: Livewire 3 + Alpine.js + Blade + TailwindCSS
- **BD**: MySQL (XAMPP local / LucusHost producción)
- **Multi-tenant**: `stancl/tenancy`
- **Permisos**: `spatie/laravel-permission`
- **Logs**: `spatie/laravel-activitylog`
- **Media**: `spatie/laravel-medialibrary`
- **PDF**: `carlos-meneses/laravel-mpdf`
- **Excel**: `maatwebsite/excel`
- **Charts**: Chart.js
- **Fechas**: Flatpickr
- **Firma**: Canvas API nativa + Alpine.js

📄 Ver detalle completo en [`STACK.md`](./STACK.md).

## 📋 Documentación

| Archivo | Contenido |
|---|---|
| [`STACK.md`](./STACK.md) | Librerías y justificación |
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | Modelo de datos, multi-tenant, flujos |
| [`ROADMAP.md`](./ROADMAP.md) | Las 7 fases de desarrollo |
| [`PERMISOS.md`](./PERMISOS.md) | Roles, permisos, niveles |
| [`FLUJOS.md`](./FLUJOS.md) | Flujos de albarán, firma, token email |
| [`CONVENCIONES.md`](./CONVENCIONES.md) | Naming, ramas, commits |
| [`INSTALACION.md`](./INSTALACION.md) | Guía instalación local XAMPP |

## 🚀 Instalación rápida

Ver [`INSTALACION.md`](./INSTALACION.md) para guía detallada.

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

Ver [`CONVENCIONES.md`](./CONVENCIONES.md).

## 📞 Contacto

- **Owner**: desarrollojhorman-blip
- **Cliente piloto**: Elecind
- **Hosting**: LucusHost