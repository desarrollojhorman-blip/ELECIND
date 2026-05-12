# 🏛️ ARQUITECTURA — ELECIND

## 🌐 Arquitectura general

Aplicación Laravel 12 **single-tenant** (una sola base de datos para Elecind).

```
┌─────────────────────────────────────────────────────────┐
│                  USUARIOS FINALES                       │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐    │
│  │ Trabajadores │  │ Responsables │  │  Admins     │    │
│  │   (móvil)    │  │ (móvil/token)│  │   (web)     │    │
│  └──────┬───────┘  └──────┬───────┘  └──────┬──────┘    │
└─────────┼─────────────────┼─────────────────┼───────────┘
          │                 │                 │
          ▼                 ▼                 ▼
   ┌─────────────────────────────────────────────┐
   │      App Laravel 12 — elecind.com           │
   │   ┌──────────┐ ┌──────────┐ ┌──────────┐    │
   │   │  Móvil   │ │   Web    │ │   API    │    │
   │   │ Livewire │ │ Livewire │ │ Sanctum  │    │
   │   └──────────┘ └──────────┘ └──────────┘    │
   └────────────────────┬────────────────────────┘
                        │
                        ▼
              ┌──────────────────┐
              │  BD MySQL única  │
              │   `elecind`      │
              │                  │
              │  - users         │
              │  - albaranes     │
              │  - materiales    │
              │  - ...           │
              └──────────────────┘
```

> 💡 **Nota futura**: el código se organiza pensando en que mañana, si se quisiera, se pueda introducir multi-tenant. Pero NO es objetivo actual.

## 🗂️ Estructura de carpetas (Laravel 12)

```
ELECIND/
├── app/
│   ├── Console/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Web/            # panel admin
│   │   │   ├── Mobile/         # panel móvil
│   │   │   └── Api/            # API
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Livewire/
│   │   ├── Tables/             # DataTables reutilizables
│   │   ├── Albaranes/
│   │   ├── Ausencias/
│   │   ├── Incidencias/
│   │   └── ...
│   ├── Models/
│   │   ├── User.php
│   │   ├── Albaran.php
│   │   ├── MaterialLote.php
│   │   └── ...
│   ├── Services/
│   ├── Policies/
│   ├── Notifications/
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── web.blade.php
│   │   │   └── mobile.blade.php
│   │   ├── livewire/
│   │   ├── web/
│   │   ├── mobile/
│   │   ├── pdfs/
│   │   └── emails/
│   ├── js/
│   ├── css/
│   └── lang/{es,en}/
├── routes/
│   ├── web.php
│   ├── mobile.php
│   └── api.php
└── storage/
```

## 🗄️ Modelo de datos

### 👤 Tabla `users`

```
users
├── id
├── username             ← ÚNICO + obligatorio (login)
├── password             ← obligatorio
├── nombre               ← obligatorio
├── apellidos            ← opcional
├── email                ← opcional, aviso si duplicado (NO bloqueante)
├── dni                  ← opcional, aviso si duplicado
├── cif                  ← opcional, aviso si duplicado
├── telefono             ← opcional
│
├── tipo_usuario         ← 'interno' | 'externo'
├── empresa_cliente_id   ← NULL si interno, FK si externo
│
├── role_id              [spatie/laravel-permission]
├── acceso               ← 'web' | 'movil' | 'ambos'
├── activo               ← boolean
├── preferencias_notificaciones (JSON)
│
├── deleted_at, deleted_by  ← soft delete
└── snapshot_data (JSON, conserva datos al borrar)
```

**Reglas de unicidad:**

| Campo | Unicidad | Obligatorio |
|---|---|---|
| `username` | ✅ Único (login) | ✅ Sí |
| `password` | — | ✅ Sí |
| `nombre` | ❌ Puede repetirse | ✅ Sí |
| `apellidos` | ❌ | ❌ |
| `email` | ⚠️ Aviso al duplicar | ❌ |
| `dni` | ⚠️ Aviso al duplicar | ❌ |
| `cif` | ⚠️ Aviso al duplicar | ❌ |
| `telefono` | ❌ | ❌ |

**Autosugerencia de username** al crear:
- Si admin escribe nombre "Juan" → sistema sugiere `juan`.
- Si ya existe → `juan.2`, `juan.3`, etc.
- Editable.

**Diferenciación interno vs externo:**

| Caso | tipo_usuario | empresa_cliente_id | Notas |
|---|---|---|---|
| Empleado de Elecind | `interno` | `NULL` | Aparece en sección "Usuarios". |
| Responsable de cliente Aluan | `externo` | `15` (id de Aluan) | Aparece en "Terceros → Aluan → Responsables". |

### 🔐 Roles y permisos

```
roles                    [spatie/laravel-permission, ampliada]
├── id, nombre
├── nivel                ← 100=superadmin, 50=admin, 10=trabajador/responsable
├── acceso               ← 'web' | 'movil' | 'ambos'
└── es_sistema           ← bool (no editable si true)

permissions              [spatie/laravel-permission]
role_has_permissions     [pivot]
```

### 🏢 Clientes y proyectos

```
empresas_clientes
├── id, nombre, cif
├── direccion, telefono, email
├── correo_notificaciones
└── deleted_at

tipos_proyecto
├── id, nombre, empresa_cliente_id

proyectos
├── id, nombre, tipo_proyecto_id
├── responsable_principal_id (FK users)

proyecto_usuario    [pivot]  -- trabajadores y responsables asignados
proyecto_material   [pivot]  -- materiales asignados
proyecto_concepto   [pivot]  -- conceptos asignados (catálogo global filtrado)
```

### 🏷️ Conceptos (catálogo global)

```
conceptos                        ← catálogo único de toda la app
├── id
├── nombre (único)
├── descripcion
└── deleted_at
```

**Funcionamiento:**
- **Catálogo global** gestionado desde `Configuración → Conceptos`.
- **Pivot N:M** con proyectos (`proyecto_concepto`): cada proyecto tiene su subconjunto de conceptos asignados.
- **Creación rápida** desde el formulario de proyecto (botón "+ Crear concepto"): se añade al catálogo Y se asigna automáticamente al proyecto actual.
- **En el albarán**: select filtrado solo con los conceptos del proyecto + opción "Otro" (texto libre).
- **Soft delete**: si un concepto se elimina, los albaranes que lo usen mantienen snapshot del nombre.

### 📦 Materiales y stock

```
materiales
├── id, grupo, nombre, unidad_medida
├── stock_minimo, notificar_stock_bajo

material_lotes
├── id, material_id
├── proveedor, n_pedido
├── stock_disponible, stock_inicial
├── fecha_entrada, stock_minimo_lote
└── deleted_at

movimientos_stock
├── id, material_lote_id
├── tipo (entrada/salida/ajuste)
├── cantidad, motivo, usuario_id
├── albaran_id (nullable)
└── created_at
```

### 📄 Albaranes (cabecera + líneas + firmas)

**Modelo basado en cabecera + tablas de líneas separadas por tipo + tabla aparte para firmas (evento legal auditable).**

```
albaranes                         ← cabecera
├── id, numero_completo, numero_correlativo
├── tipo (normal/personalizado/web_adjunto)
├── estado (borrador/pendiente_firma/firmado_parcial/
│          firmado/terminado/facturado/anulado)
├── proyecto_id, empresa_cliente_id (nullables si avanzado)
├── creador_id, concepto_id, concepto_libre
├── tipo_horas (laboral_dia/noche/festivo_dia/noche)
├── fecha, observaciones
├── geolocalizacion_lat, geolocalizacion_lng
├── datos_libres_json   (campos "Otro" pendientes de normalizar)
└── timestamps

albaran_lineas_personal           ← líneas de trabajadores
├── id, albaran_id, usuario_id
├── horas_normales, horas_extras
├── es_creador

albaran_lineas_material           ← líneas de materiales
├── id, albaran_id, material_lote_id, cantidad
├── descripcion_libre (si avanzado)

albaran_firmas                    ← evento legal auditable (aparte)
├── id, albaran_id, tipo (trabajador/responsable)
├── firmado_por_usuario_id, firmante_asignado_id
├── metodo_firma (cuenta_propia/cuenta_prestada/token_email)
├── media_id (PNG via medialibrary)
├── fecha, ip, geolocalizacion
├── token_hash, token_expira_at, token_usado_at
├── estado (activa/caducada/invalidada)
```

### 🆘 Ausencias e incidencias

```
ausencias
├── id, usuario_id, tipo
├── descripcion, fecha_inicio, fecha_fin
├── hora_inicio, hora_fin (nullables)
├── estado_id, aprobado_por, fecha_aprobacion

incidencias
├── id, usuario_id, tipo (albaran/ausencia/general/stock_bajo)
├── albaran_id, ausencia_id (nullables)
├── titulo, descripcion, estado_id
├── asignado_a (nullable)

estados_configurables
├── id, entidad (ausencia/incidencia)
├── nombre, color, orden, es_final
```

### ⚙️ Configuración y logs

```
configuracion_empresa  (1 sola fila)
├── nombre, cif, direccion, telefono, email, web
├── color_primario, color_secundario
├── plantilla_numeracion_albaran
├── token_caducidad_dias
├── notificaciones_email_destino
├── plantilla_pdf_config (JSON: colores líneas, colores texto, toggles campos)

logs_auditoria [spatie/laravel-activitylog]

notificaciones_enviadas
├── id, destinatario, asunto, plantilla
├── contexto_json, enviado_at
```

### 📁 Media (polimórfica)

```
media [spatie/laravel-medialibrary]
├── id, model_type, model_id, collection_name
├── file_name, mime_type, size, ...
```

## 🔢 Sistema de numeración configurable

Plantilla con variables sustituibles:

| Variable | Significado | Ejemplo |
|---|---|---|
| `{AÑO}` | Año 4 dígitos | 2026 |
| `{AÑO2}` | Año 2 dígitos | 26 |
| `{MES}` | Mes 2 dígitos | 05 |
| `{DIA}` | Día 2 dígitos | 11 |
| `{NUM:N}` | Correlativo N dígitos | 00032 |
| `{CIF}` | CIF empresa | B12345678 |

**Ejemplos:**
- `ALB-{AÑO}-{NUM:5}` → `ALB-2026-00032`
- `ALB{AÑO2}{MES}-{NUM:4}` → `ALB2605-0032`
- `PARTE-{NUM:6}` → `PARTE-000032`

**Reset del correlativo**: configurable (anual / mensual / nunca).

## 🛡️ Seguridad

- **Permisos server-side**: Livewire ejecuta todo con sesión autenticada (no expone JSON).
- **Soft delete global**: nada se borra físicamente.
- **Auditoría completa** vía Activity Log.
- **Tokens de firma**: caducan a 7 días (configurable) O al usarse (lo que ocurra primero).
- **Snapshots** en albaranes: si se borra un usuario, los datos visibles del albarán se conservan.
- **Login por username** (no email): permite emails duplicados sin comprometer login.
- **Firmas en tabla aparte**: trazabilidad legal completa (historial, reintentos, anulaciones).