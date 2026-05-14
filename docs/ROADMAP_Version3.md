# 🗺️ ROADMAP — ELECIND

Desarrollo por fases. Cada fase = una rama desde `develop` → PR a `develop` cuando esté lista → merge a `main` en hitos estables.

---

## ✅ Fase 0 — Setup inicial (1 semana)

**Rama:** `fase-0-setup`

### Objetivos
- Proyecto **Laravel 12** funcionando en XAMPP local.
- Repositorio organizado.
- Stack base instalado.

### Tareas
- [x] `composer create-project laravel/laravel:^12.0`
- [x] Configurar `.env.example` + `.env` con BD local
- [x] Instalar TailwindCSS + Livewire 4 + Alpine
- [x] Instalar `spatie/laravel-permission`
- [x] Instalar `spatie/laravel-activitylog`
- [x] Instalar `spatie/laravel-medialibrary`
- [x] Instalar `carlos-meneses/laravel-mpdf`
- [x] Instalar `maatwebsite/excel`
- [x] Crear estructura de carpetas
- [x] Layouts base: `web` y `mobile`
- [x] CI/CD básico (workflows GitHub Actions: lint + tests)
- [x] Configurar Pint + Larastan
- [x] Login + middleware web/móvil

### Entregable
- [x] App levantada con login básico (por username) y dos layouts (web y móvil).

---

## ✅ Fase 1 — MVP base: usuarios, clientes, proyectos, materiales (3-4 semanas)

**Rama:** `fase-1-mvp-base`
**Estado:** ✅ COMPLETADA + EXTENDIDA (13/05/2026 — 14/05/2026)

### Objetivos
- CRUD completos de entidades base.
- Sistema de permisos funcionando.
- Configuración empresa con logo y colores.

### Tareas
- [x] Modelos + migraciones base del dominio Fase 1
  - [x] Clientes *(antes `EmpresasClientes`, renombrado)*
  - [x] TiposProyecto, Proyectos
  - [x] Materiales, MaterialLotes, MovimientosStock
  - [x] Conceptos (catálogo global + pivot N:M con proyectos)
  - [x] Empresa *(singleton, antes `ConfiguracionEmpresa`, renombrado)*
  - [x] Relaciones base con Users (`cliente_id` —antes `empresa_cliente_id`—, responsable principal y asignaciones a proyecto)
- [x] Factories + seeders de entidades Fase 1
- [x] Extender Roles y Permissions con `nivel` + `acceso` + `es_sistema` + `ambito` + `descripcion` + `categoria`
- [x] ~~Componente Livewire `<livewire:data-table />` reutilizable~~ → **Sustituido por catálogo de componentes Blade UI reutilizables** (`<x-ui.button>`, `<x-ui.modal>`, `<x-ui.search-and-filter>`, `<x-ui.data-table>`, `<x-ui.actions-menu>`, `<x-ui.sidebar>`, etc.) más composables y testables.
- [x] **Sistema de diseño base** (no estaba en lista original): paleta semántica (verde/rojo/azul), tokens CSS vars overridables, Heroicons, Branding helper, layout web con sidebar colapsable y dropdown de avatar.
- [x] CRUD: Clientes *(antes Empresas clientes)* — alta/edición/soft delete/listado/filtros plegables/chips/búsqueda con debounce/exportar disabled "Pronto" + campo `numero_cliente` único
- [x] **CRUD: Usuarios** (autosugerencia username, avisos no-bloqueantes de duplicados email/DNI/CIF, jerarquía por nivel, scoping en listado)
- [x] ~~CRUD: Tipos proyecto~~ + **CRUD Proyectos** *(tipos se gestionan al vuelo desde el form de Proyecto via select + botón "+", no requieren pantalla propia)*
- [x] **CRUD: Materiales + entrada de stock (lotes)** *(2 pantallas: `/materiales` catálogo + `/materiales/{id}/lotes` con migaja, barras de progreso de stock, alertas de caducidad)*
- [x] **CRUD: Conceptos** (catálogo global + pivot N:M con proyectos preparado para Fase 2)
- [x] **Pantalla "Configuración Empresa"** (logo upload + colores en runtime sin rebuild + plantilla numeración + caducidad token firma)
- [x] Política de soft delete *(implementada en todos los CRUDs con restore desde filtro "papelera")*
- [x] Login + middleware web/móvil **+ refactor: middleware lee acceso del rol (fuente única, bug `mobile`→`movil` corregido)**
- [x] **CRUD Roles y permisos personalizados** *(extensión post-cierre, antes pospuesto a Fase 4)* — con filtro por nivel + ámbito web/móvil/ambos + regla de delegación "no delegues más de lo que tienes" + protección de roles del sistema + reset al cambiar ámbito + toggle "marcar todos por categoría" + ordenamiento web→móvil→ambos dentro de cada categoría
- [x] **Refactor de nombres** *(consolidación pre-Fase 2)*:
  - Tablas: `empresas_clientes` → `clientes`, `configuracion_empresa` → `empresa`
  - Modelos: `EmpresasCliente` → `Cliente`, `ConfiguracionEmpresa` → `Empresa`
  - Columna FK: `users.empresa_cliente_id` → `users.cliente_id`, `proyectos.empresa_cliente_id` → `proyectos.cliente_id`
  - Relaciones: `$user->empresaCliente` → `$user->cliente`, idem en `Proyecto`
  - Policies, Forms, Livewire components, factory, seeder, tests y blade — todo coherente

### Entregable
✅ Admin puede configurar toda la base de datos antes de empezar a crear albaranes.

### Estado final *(actualizado 14/05/2026 — extensión + refactor + familias)*
- [x] Fase 0 cerrada y validada
- [x] Base de datos completa: 12 tablas + pivots + `empresa` singleton (con migraciones de renombre incluidas)
- [x] Seeders demo operativos (5 clientes · 6 tipos · 15 conceptos · **6 pedidos · 4 familias · 34 materiales (~70% con familia)** · 12 proyectos · 8 trabajadores · 5 responsables · 1 trabajador demo estable `trabajador/password`)
- [x] Sistema de diseño + 18 componentes Blade UI reutilizables
- [x] **10 CRUDs operativos**: Clientes · Proyectos · **NúmerosPedido** · Materiales · **Familias de Material** · Usuarios · Conceptos · Empresa · **Roles y permisos** · *(MaterialLotes eliminado en refactor)*
- [x] **10 Policies** registradas (incluida `FamiliaMaterialPolicy` y `NumeroPedidoPolicy` que sustituyen a `MaterialLotePolicy`)
- [x] **201 tests feature** · 643 assertions · Pint OK · Larastan 0 errores
- [x] **Catálogo de 62 permisos** clasificados por ámbito (web/movil/ambos) y categoría con descripciones inequívocas (incluidos los 4 nuevos `materiales.familias.*` y los 4 de `pedidos.*`)

### Avance Fase 1: 100 % ✅ + extensión CRUD Roles + refactor materiales + Familias

Ver detalles en:
- [`docs/resumen/120526_1500_avance_fase_1.md`](./resumen/120526_1500_avance_fase_1.md) (iter. 1: BD + seeders)
- [`docs/resumen/130526_1800_avance_fase_1.md`](./resumen/130526_1800_avance_fase_1.md) (iter. 2: UI base + Clientes)
- [`docs/resumen/130526_0300_avance_fase_1.md`](./resumen/130526_0300_avance_fase_1.md) (iter. 3: Proyectos + Materiales)
- [`docs/resumen/130526_2300_cierre_fase_1.md`](./resumen/130526_2300_cierre_fase_1.md) (iter. 4 — cierre Fase 1)
- [`docs/resumen/140526_0100_extension_roles_y_refactor.md`](./resumen/140526_0100_extension_roles_y_refactor.md) (iter. 5 — **extensión post-cierre**: CRUD Roles + refactor de nombres)
- [`docs/resumen/140526_2200_familias_y_estado_actual.md`](./resumen/140526_2200_familias_y_estado_actual.md) (**resumen consolidado**: refactor materiales, refactor horas, Familias, estado actual + pendientes)

### 🔧 Extensiones post-cierre (mayo 2026)

Cambios estructurales realizados después del cierre formal de Fase 1, mientras se trabajaba en Fase 2:

- [x] **Refactor materiales/lotes → NumeroPedido + Material plano** *(2026-05-14)*
  - Eliminadas tablas `material_lotes` y `movimientos_stock`.
  - Materiales pasan a tener `stock` directo (sin lotes intermedios) y FK `numero_pedido_id`.
  - Nueva tabla `numero_pedidos` (numero único, fecha, proveedor, descripción) con CRUD propio en `/materiales/pedidos`.
  - `albaran_lineas_material.material_lote_id` → `material_id`.
  - Observer `AlbaranLineaMaterialObserver` ajusta `material.stock` directamente.
  - Pivot `material_proyecto` recreado tras eliminarse por error (sin `cantidad_prevista`).
- [x] **Refactor horas en Albaran** *(2026-05-14)*
  - Eliminado `tipo_hora` por línea (4 enums).
  - `Albaran` gana `tipo_dia` (laborable/festivo) en cabecera.
  - `AlbaranLineaPersonal.horas` se descompone en `horas` (normales) + `horas_extra` (extras).
- [x] **CRUD Familias de Material** *(2026-05-14)*
  - Tabla `familias_material` (nombre único + descripción + soft delete).
  - FK `materiales.familia_id` nullable (nullOnDelete).
  - CRUD propio en `/materiales/familias` con modal "Asignar materiales" (toggle huérfanos vs todos).
  - 4 permisos `materiales.familias.{ver,crear,modificar,eliminar}`.
  - Listado de materiales: nueva columna + filtro por familia (incl. opción "Sin familia").
  - **⚠️ UX del modal "Asignar materiales" pendiente de rework** → ver [resumen](./resumen/140526_2200_familias_y_estado_actual.md): hay que sustituir el modal-dentro-de-modal por un select inline + botón "Añadir" desde el panel de materiales de la familia.
- [x] **Login redirect por rol** *(2026-05-14)*
  - Trabajadores intentando entrar en `/login` con rol móvil van directos al dashboard móvil.
  - Admins/superadmins van al dashboard web.

---

## 🚧 Fase 2 — Albaranes core + firma (3 semanas)

**Rama:** `fase-2-albaranes`
**Estado:** 🚧 EN CURSO (Iter. 1, 2 y 3 de 6 completadas — ~50 %)
**Orden estratégico:** móvil primero, web después (decisión 14/05/2026).

### Objetivos
- Crear/firmar albaranes desde móvil y web.
- PDF generado y exportable.
- Tokens de firma por email.

### División en 6 iteraciones

#### ✅ Iter. 1 — Núcleo de datos (completada 14/05/2026)
- [x] Migración con 5 tablas: `albaranes`, `albaran_lineas_personal`, `albaran_lineas_material`, `albaran_firmas`, `albaran_tokens_firma`
- [x] Enums: `EstadoAlbaran` (con transiciones), `TipoHora` (4 tipos), `TipoFirma`
- [x] 5 Modelos Eloquent con relaciones, casts, soft deletes en `Albaran`
- [x] Observer `AlbaranLineaMaterialObserver` para descuento automático de stock (created/updated/deleted, con `lockForUpdate`)
- [x] Servicio `NumeracionService` con plantilla configurable (`{YYYY}`, `{YY}`, `{MM}`, `{NNNN}`, `{NNN}`, `{NN}`)
- [x] Factories de los 5 modelos con states (firmado/facturado/archivado/usado/caducado/invalidado)
- [x] `Fase2DemoSeeder` con 5 albaranes en estados distintos + líneas + firmas + tokens
- [x] 4 permisos nuevos: `albaranes.descargar_pdf` (ambos), `albaranes.solicitar_firma` (web), `albaranes.invalidar_firma` (web · solo superadmin), `albaranes.facturar` (web)
- [x] 19 tests nuevos (7 EstadoAlbaran + 6 NumeracionService + 6 Observer) · 145 totales

#### ✅ Iter. 2 — Infraestructura móvil (completada 14/05/2026)
- [x] Layout `components.layouts.mobile` con header (logo + back + título) y sin nav inferior por ahora
- [x] Rutas `/mobile/...` (sin segmento `/movil` para mantener consistencia ES/EN) con middleware `ensure.mobile.access`
- [x] Dashboard móvil (`/mobile/dashboard`) con "Mis partes recientes" + atajo "Nuevo parte"
- [x] Componentes Blade UI móviles (`<x-mobile.header>`, `<x-mobile.menu-action>`, `<x-mobile.button>`, etc.)
- [x] Login redirect por rol: trabajador → `mobile.dashboard`; admin/superadmin → `web.dashboard`

#### ✅ Iter. 3 — CRUD albarán desde móvil (completada 14/05/2026)
- [x] Pantalla "Nuevo Parte de Trabajo" (`Mobile\Albaranes\Crear`) optimizada táctil
- [x] Pantalla "Ver parte" (`Mobile\Albaranes\Ver`) con líneas de personal y material
- [x] Selects dependientes (proyecto → conceptos del proyecto → usuarios del proyecto → materiales del proyecto)
- [x] Líneas de personal: yo + compañeros, con `horas` + `horas_extra` (refactor post-iter)
- [x] Líneas de material: select Material directo (sin lotes, post-refactor) + cantidad
- [x] Cabecera: `tipo_dia` (laborable/festivo) en cabecera (refactor post-iter)
- [x] Guardar como borrador (estado por defecto), editar borradores propios
- [x] Permisos visibles para trabajadores: solo ven proyectos donde están asignados (o son responsable principal)

#### ⏳ Iter. 4 — Firma + flujo legal
- [ ] Componente firma Canvas + Alpine (PNG en storage)
- [ ] Doble firma presencial in-situ
- [ ] Generación de token email + ruta pública `/firmar/{token}` sin auth
- [ ] Transiciones: borrador → pendiente_firma → firmado
- [ ] Activity log + geolocalización opcional
- [ ] Mailable de notificación al responsable
- [ ] Generación PDF con mPDF

#### ⏳ Iter. 5 — CRUD web del admin
- [ ] Pantalla `/albaranes` con tabla + filtros + modal alta/edición
- [ ] Cambio de firmantes asignados (`creado_por`, `responsable_id`) si hueco vacío
- [ ] Modal "Solicitar firma" con selector (trabajador/responsable/email custom)
- [ ] Gestión de tokens (reenviar/regenerar/invalidar)
- [ ] Eliminar firmas con `albaranes.invalidar_firma` + activity log
- [ ] Forzar transiciones de estado con permiso
- [ ] Descargar PDF desde tabla

#### ⏳ Iter. 6 — Refinamiento + adjuntos
- [ ] Adjuntos múltiples vía medialibrary (PDFs externos, fotos)
- [ ] Snapshot de datos al firmar (auditoría histórica)
- [ ] Tests adicionales y edge cases

### Entregable
- Trabajador crea albarán desde móvil → firma → email al responsable → responsable firma vía link → PDF generado.

### Decisiones cerradas (10)
| # | Decisión |
|---|---|
| 1 | Móvil primero, web después |
| 2 | ~~4 tipos de hora~~ → **revisado 14/05**: `tipo_dia` (laborable/festivo) en cabecera + `horas` + `horas_extra` por línea personal |
| 3 | Stock descuenta al crear/editar la línea de material (Observer). **Ahora ajusta `material.stock` directo** (los lotes se eliminaron en refactor) |
| 4 | Estados: borrador → pendiente_firma → firmado → facturado → archivado |
| 5 | Token de firma: single-use + caducidad por tiempo |
| 6 | 2 huecos de firma (trabajador + responsable). Cualquiera con token puede firmar (el token sabe a qué hueco va) |
| 7 | Geolocalización opcional con prompt del navegador |
| 8 | Modal "Solicitar firma" con selector (trabajador / responsable del proyecto / email custom) |
| 9 | Sin pedir nombre/DNI en firma pública. Borrar firmas = borrar imagen + activity log |
| 10 | **Materiales sin lotes**: stock directo en `materiales.stock` + agrupador opcional `familia_id` *(decisión post-cierre Fase 1)* |

Ver detalle de iteración 1 en [`docs/resumen/140526_1500_iter1_fase2_nucleo_datos.md`](./resumen/140526_1500_iter1_fase2_nucleo_datos.md).
Ver estado consolidado tras Iter. 2-3 + extensiones en [`docs/resumen/140526_2200_familias_y_estado_actual.md`](./resumen/140526_2200_familias_y_estado_actual.md).

---

## 🔄 Fase 3 — Albarán personalizado (avanzado) (2-3 semanas)

**Rama:** `fase-3-albaran-avanzado`

### Objetivos
- Crear albaranes cuando faltan datos en BD.
- Vista de normalización para admin.

### Tareas
- [ ] **Móvil**: pantalla "Parte personalizado"
  - Selects con opción "Otro" → campo texto libre
  - Materiales como texto (sin descuento)
- [ ] Guardado como `borrador` con `datos_libres_json`
- [ ] Permitir firma aunque sea borrador
- [ ] **Web**: vista de revisión de borrador
  - Campos inteligentes: select con valores libres + botón "+ Crear"
  - Modales de creación rápida de cliente / proyecto / responsable / material / concepto
  - Conversión de concepto libre a concepto formal
- [ ] Validación al pasar de `borrador` → `pendiente_firma`/`firmado`

### Entregable
- Albarán personalizado creado en móvil → admin lo normaliza desde web → albarán formal.

---

## 🆘 Fase 4 — Ausencias e incidencias (2 semanas)

**Rama:** `fase-4-ausencias-incidencias`

### Objetivos
- CRUD ausencias con aprobación.
- CRUD incidencias con fotos.

### Tareas
- [ ] Modelo `Ausencia` (días + horas)
- [ ] Modelo `Incidencia` (polimórfica: albarán/ausencia/general/stock_bajo)
- [ ] CRUD estados configurables (nombre + color)
- [ ] **Móvil**: solicitar ausencia
- [ ] **Móvil**: crear incidencia desde menú "⋮" (con contexto si está en albarán/ausencia)
- [ ] **Móvil**: subir fotos (medialibrary)
- [ ] **Web**: gestión completa de ausencias + flujo de aprobación
- [ ] **Web**: gestión completa de incidencias
- [ ] Notificaciones email configurables (toggles por usuario)
- [ ] Alertas automáticas de stock bajo → email al admin + incidencia auto
- [ ] Vista de incidencias asociadas dentro de albarán/ausencia

> 📌 **Nota**: El CRUD de Roles personalizados se anticipó como extensión de Fase 1 (ya operativo). En Fase 4 podría revisarse la regla *"Acceso por defecto al asignar usuario"* (¿el `acceso` del usuario se hereda del rol elegido al asignarlo?) si el cliente la requiere durante la operativa de ausencias/aprobaciones.

### Entregable
- Flujo completo de ausencias e incidencias.

---

## 📊 Fase 5 — Reportes y exportación (2 semanas)

**Rama:** `fase-5-reportes`

### Objetivos
- Resumen mensual del trabajador.
- Exportaciones Excel/PDF.

### Tareas
- [ ] **Móvil**: pantalla "Resumen mensual"
  - Filtro por fecha
  - Total horas por tipo (laboral/festivo/normal/extra)
  - Total horas por proyecto
  - Ausencias del mes
  - Gráficos con Chart.js
- [ ] **Web**: Control de Horas (tabla por usuario y rango)
- [ ] Exportar Excel:
  - Albaranes
  - Control de horas
  - Ausencias
  - Materiales / movimientos stock
  - Clientes
- [ ] Imprimir albarán (PDF) desde tabla
- [ ] Preparar (sin implementar) exportación formato Factusol

### Entregable
- Reportes operativos. **App lista para usar en producción en Elecind.**

---

## 🔌 Fase 6 — API e integraciones (futuro)

**Rama:** `fase-6-api`

### Objetivos
- API REST documentada.
- Preparado para integraciones (Sage, Holded, Factusol).

### Tareas
- [ ] API REST con Sanctum
- [ ] Tokens API con permisos granulares por endpoint
- [ ] Documentación OpenAPI/Swagger
- [ ] Webhooks salientes (al crear albarán, firmar, etc.)
- [ ] Exportación formato Factusol (Excel estándar)
- [ ] Integración Factusol (si cliente confirma)
- [ ] Rate limiting

### Entregable
- API operativa documentada.

---

## 🌐 Fase 7 — Multi-tenant SaaS (opcional, futuro lejano)

**Rama:** `fase-7-saas`

> ⚠️ **NO es objetivo actual.** Solo se valorará si después del MVP en Elecind se decide vender la app a otras empresas.

### Posibles tareas (si se decide)
- Migrar a `stancl/tenancy` con BD por tenant.
- Crear panel central de gestión SaaS.
- Integración pagos / suscripciones.
- Alta automática de tenants vía API LucusHost.

---

## 📅 Resumen de tiempos estimados

| Fase | Tiempo | Acumulado |
|---|---|---|
| Fase 0 | 1 sem | 1 sem |
| Fase 1 | 3-4 sem | 4-5 sem |
| Fase 2 | 3 sem | 7-8 sem |
| Fase 3 | 2-3 sem | 9-11 sem |
| Fase 4 | 2 sem | 11-13 sem |
| Fase 5 | 2 sem | 13-15 sem |
| **MVP listo para Elecind** | | **≈ 4 meses** |
| Fase 6 | A demanda | — |
| Fase 7 | A futuro | — |