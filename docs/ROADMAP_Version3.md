# 🗺️ ROADMAP — ELECIND

Desarrollo por fases. Cada fase = una rama desde `develop` → PR a `develop` cuando esté lista → merge a `main` en hitos estables.

---

## ✅ Fase 0 — Setup inicial (1 semana)

**Rama:** `fase-0-setup`

### Objetivos
- Proyecto **Laravel 12** funcionando en XAMPP local.
- Subdominios `getradi.test` operativos.
- Multi-tenant configurado con 1 tenant (Elecind).
- Repositorio organizado.

### Tareas
- [ ] `composer create-project laravel/laravel:^12.0`
- [ ] Configurar `.env.example` + `.env` con BD local
- [ ] Instalar TailwindCSS + Livewire 3 + Alpine
- [ ] Instalar `stancl/tenancy` + configurar BD central + BD tenant
- [ ] Instalar `spatie/laravel-permission`
- [ ] Instalar `spatie/laravel-activitylog`
- [ ] Instalar `spatie/laravel-medialibrary`
- [ ] Configurar virtualhost Apache + hosts file
- [ ] Crear estructura de carpetas
- [ ] Layouts base: `web`, `mobile`, `central`
- [ ] CI/CD básico (workflows GitHub Actions: lint + tests)
- [ ] Configurar Pint + Larastan

### Entregable
- App levantada en `http://admin.getradi.test` y `http://elecind.getradi.test` con login básico (por username).

---

## 🏗️ Fase 1 — MVP base: usuarios, clientes, proyectos, materiales (3-4 semanas)

**Rama:** `fase-1-mvp-base`

### Objetivos
- CRUD completos de entidades base.
- Sistema de permisos funcionando.
- Configuración empresa con logo y colores.

### Tareas
- [ ] Modelos + migraciones + factories + seeders
  - Users (con `username` único, autosugerencia, `tipo_usuario` interno/externo)
  - Roles, Permissions
  - EmpresasClientes
  - TiposProyecto, Proyectos
  - Materiales, MaterialLotes, MovimientosStock
  - Conceptos
- [ ] Componente Livewire `<livewire:data-table />` reutilizable
- [ ] CRUD: Usuarios (con roles y niveles, autosugerencia username, avisos duplicado email/dni/cif)
- [ ] CRUD: Empresas clientes (con responsables = usuarios externos)
- [ ] CRUD: Tipos proyecto + Proyectos (con usuarios/materiales/conceptos asignados)
- [ ] CRUD: Materiales + entrada de stock (lotes)
- [ ] CRUD: Conceptos
- [ ] Pantalla "Configuración empresa" (logo, colores, datos, plantilla numeración)
- [ ] Política de soft delete
- [ ] Login + middleware web/móvil/superadmin
- [ ] CRUD roles personalizados con filtro por nivel

### Entregable
- Admin puede configurar toda la base de datos antes de empezar a crear albaranes.

---

## 📄 Fase 2 — Albaranes core + firma (3 semanas)

**Rama:** `fase-2-albaranes`

### Objetivos
- Crear/firmar albaranes desde móvil y web.
- PDF generado y exportable.
- Tokens de firma por email.

### Tareas
- [ ] Modelo `Albaran` + relaciones (participantes, materiales, firmas, documentos)
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
- [ ] Notificaciones email configurables (toggles por tenant y por usuario)
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
- Reportes operativos para el cliente. **MVP listo para usar en Elecind.**

---

## 🌐 Fase 6 — SaaS multi-tenant completo (3 semanas)

**Rama:** `fase-6-saas`

### Objetivos
- Alta automática de tenants.
- Panel central de gestión SaaS.

### Tareas
- [ ] BD central completa: tenants, planes, suscripciones
- [ ] Panel `admin.getradi.es`:
  - CRUD tenants
  - CRUD planes (precio, max usuarios, features)
  - Vista suscripciones + estado pagos
  - Logs centrales
- [ ] Integración API LucusHost (crear BD + subdominio)
- [ ] Recepción de webhook desde web SaaS principal cuando se paga
- [ ] Formulario alta empresa (datos + logo + admin inicial)
- [ ] Email con credenciales + URL al admin del nuevo tenant
- [ ] Feature flags por plan (esconder módulos según licencia)
- [ ] Validación de límite de usuarios activos por plan (solo cuentan `tipo_usuario='interno'`)
- [ ] Personalización por tenant: login con logo + colores
- [ ] Soporte multi-idioma (ES/EN) preparado

### Entregable
- SaaS funcionando: pagos en web principal → tenant creado automáticamente → cliente logueado en su subdominio.

---

## 🔌 Fase 7 — API e integraciones (futuro)

**Rama:** `fase-7-api`

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
| Fase 6 | 3 sem | 16-18 sem |
| Fase 7 | A demanda | — |