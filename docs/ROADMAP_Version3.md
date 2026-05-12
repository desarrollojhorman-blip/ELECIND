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

## 🚧 Fase 1 — MVP base: usuarios, clientes, proyectos, materiales (3-4 semanas)

**Rama:** `fase-1-mvp-base`

### Objetivos
- CRUD completos de entidades base.
- Sistema de permisos funcionando.
- Configuración empresa con logo y colores.

### Tareas
- [x] Modelos + migraciones base del dominio Fase 1
  - [x] EmpresasClientes
  - [x] TiposProyecto, Proyectos
  - [x] Materiales, MaterialLotes, MovimientosStock
  - [x] Conceptos (catálogo global + pivot N:M con proyectos)
  - [x] Relaciones base con Users (`empresa_cliente_id`, responsable principal y asignaciones a proyecto)
- [ ] Factories + seeders de entidades Fase 1
- [ ] Extender Roles y Permissions con `nivel` + `acceso` + `es_sistema`
- [ ] Componente Livewire `<livewire:data-table />` reutilizable
- [ ] CRUD: Usuarios (con roles y niveles, autosugerencia username, avisos duplicado email/dni/cif)
- [ ] CRUD: Empresas clientes (con responsables = usuarios externos)
- [ ] CRUD: Tipos proyecto + Proyectos (con usuarios/materiales/conceptos asignados)
- [ ] CRUD: Materiales + entrada de stock (lotes)
- [ ] CRUD: Conceptos (catálogo global) + asignación N:M desde proyecto
- [ ] Pantalla "Configuración empresa" (logo, colores, datos, plantilla numeración)
- [ ] Política de soft delete
- [x] Login + middleware web/móvil
- [ ] CRUD roles personalizados con filtro por nivel

### Entregable
- Admin puede configurar toda la base de datos antes de empezar a crear albaranes.

### Estado actual
- [x] Fase 0 cerrada y validada
- [x] Base de datos núcleo de Fase 1 creada y migrando correctamente
- [ ] CRUD y pantallas de gestión pendientes
- [ ] Seeds de negocio pendientes

---

## 📄 Fase 2 — Albaranes core + firma (3 semanas)

**Rama:** `fase-2-albaranes`

### Objetivos
- Crear/firmar albaranes desde móvil y web.
- PDF generado y exportable.
- Tokens de firma por email.

### Tareas
- [ ] Modelo `Albaran` + tablas:
  - `albaranes` (cabecera)
  - `albaran_lineas_personal` (trabajadores + horas)
  - `albaran_lineas_material` (materiales)
  - `albaran_firmas` (evento legal auditable)
- [ ] **Móvil**: pantalla "Parte de Trabajo" (albarán normal)
  - Form Livewire con selects dependientes
  - Compañeros dinámicos (horas normales + extras opcionales)
  - Materiales con cantidad y descuento de stock
- [ ] **Móvil**: pantalla "Firmar"
  - Canvas API + Alpine
  - 2 firmas (trabajador + responsable)
  - Guardar como PNG via medialibrary
- [ ] **Web**: CRUD de albaranes con tabla + modal
- [ ] **Web**: adjuntar múltiples PDFs/imágenes del albarán y factura
- [ ] Servicio `NumeracionService` con plantillas configurables
- [ ] Estados + transiciones + bloqueo "terminado"/"facturado"
- [ ] Notificación email al crear albarán (con o sin token)
- [ ] Flujo de **token email** para firma sin cuenta
  - Generación + invalidación al usar o caducar
  - Vista pública para firmar
- [ ] Generación PDF con mPDF (plantilla configurable: colores, toggles campos)
- [ ] Geolocalización al firmar
- [ ] Activity log de todas las acciones críticas

### Entregable
- Trabajador crea albarán desde móvil → firma → email al responsable → responsable firma vía link → PDF generado.

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