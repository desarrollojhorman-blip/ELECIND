# 🏛️ ARQUITECTURA — ELECIND

## 🌐 Arquitectura general

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
   │   {tenant}.getradi.es — App Laravel 12      │
   │   ┌──────────┐ ┌──────────┐ ┌──────────┐    │
   │   │  Móvil   │ │   Web    │ │   API    │    │
   │   │ Livewire │ │ Livewire │ │ Sanctum  │    │
   │   └──────────┘ └──────────┘ └──────────┘    │
   │                                             │
   │   Middleware: tenant resolver (stancl)      │
   └────────────────────┬────────────────────────┘
                        │
              ┌─────────┴──────────┐
              │                    │
              ▼                    ▼
       ┌──────────────┐    ┌──────────────────┐
       │ BD CENTRAL   │    │ BD por tenant    │
       │ getradi_     │    │ tenant_elecind   │
       │ central      │    │ tenant_aluan     │
       │              │    │ ...              │
       │ - tenants    │    │ - users          │
       │ - domains    │    │ - albaranes      │
       │ - plans      │    │ - materiales     │
       │ - subs       │    │ - ...            │
       └──────────────┘    └──────────────────┘
```

## 🔀 ¿Qué es un "tenant"?

**Tenant = empresa cliente** que usa la app. Cada tenant tiene:
- Su propio subdominio (ej: `elecind.getradi.es`).
- Su propia base de datos aislada.
- Sus usuarios, albaranes, configuración, logo, colores.

`stancl/tenancy` detecta el subdominio y conecta automáticamente a la BD correcta. Imposible que un tenant vea datos de otro.

## 🗂️ Estructura de carpetas (Laravel 12)

```
ELECIND/
├── app/
│   ├── Console/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Central/        # admin.getradi.es
│   │   │   ├── Web/            # panel admin tenant
│   │   │   ├── Mobile/         # panel móvil tenant
│   │   │   └── Api/            # API tenant
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Livewire/
│   │   ├── Tables/             # DataTables reutilizables
│   │   ├── Albaranes/
│   │   ├── Ausencias/
│   │   ├── Incidencias/
│   │   └── ...
│   ├── Models/
│   │   ├── Central/
│   │   │   ├── Tenant.php
│   │   │   ├── Plan.php
│   │   │   └── Subscription.php
│   │   └── Tenant/
│   │       ├── User.php
│   │       ├── Albaran.php
│   │       ├── MaterialLote.php
│   │       └── ...
│   ├── Services/
│   ├── Policies/
│   ├── Notifications/
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   │   ├── central/
│   │   └── tenant/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── js/
│   ├── css/
│   └── lang/{es,en}/
├── routes/
│   ├── web.php
│   ├── mobile.php
│   ├── api.php
│   ├── central.php
│   └── tenant.php
└── storage/
```

## 🗄️ Modelo de datos — BD por tenant

### 👤 Tabla `users` (modelo final)

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
- Editable. El admin puede aceptar o escribir otro.

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
proyecto_concepto   [pivot]  -- conceptos asignados

conceptos
├── id, nombre, descripcion
```

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

### 📄 Albaranes

```
albaranes
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

albaran_participantes
├── id, albaran_id, usuario_id
├── horas_normales, horas_extras
├── es_creador

albaran_materiales
├── id, albaran_id, material_lote_id, cantidad
├── descripcion_libre (si avanzado)

albaran_firmas
├── id, albaran_id, tipo (trabajador/responsable)
├── firmado_por_usuario_id, firmante_asignado_id
├── metodo_firma (cuenta_propia/cuenta_prestada/token_email)
├── media_id (PNG via medialibrary)
├── fecha, ip, geolocalizacion
├── token_hash, token_expira_at, token_usado_at
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
configuracion_empresa  (1 sola fila por tenant)
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

## 🗄️ Modelo de datos — BD central

```
tenants
├── id, nombre, cif
├── db_name, plan_id, estado_suscripcion
├── fecha_alta, correo_admin_principal
├── activo

domains
├── id, domain, tenant_id

planes
├── id, nombre, precio_mensual
├── max_usuarios_activos
├── funcionalidades_json
│  (stock, incidencias, api, multi_idioma, ...)

suscripciones
├── id, tenant_id, plan_id
├── fecha_inicio, fecha_fin, estado
├── metodo_pago, ultimo_pago_at

logs_centrales
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

- **Aislamiento por tenant**: BD separada, imposible filtración entre tenants.
- **Permisos server-side**: Livewire ejecuta todo con sesión autenticada (no expone JSON).
- **Soft delete global**: nada se borra físicamente.
- **Auditoría completa** vía Activity Log.
- **Tokens de firma**: caducan a 7 días (configurable) O al usarse (lo que ocurra primero).
- **Snapshots** en albaranes: si se borra un usuario, los datos visibles del albarán se conservan.
- **Login por username** (no email): permite emails duplicados sin comprometer login.