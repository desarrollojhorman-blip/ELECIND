# 🏗️ Avance Fase 2 — Iteración 1 · Núcleo de datos de albaranes

**Fecha:** 14/05/2026
**Hora:** 03:00 (cierre de iteración)
**Estado:** ✅ ITERACIÓN 1 COMPLETADA · 145/145 tests verde · Fase 2 al ~17 %

Continuación de [`140526_0100_extension_roles_y_refactor.md`](./140526_0100_extension_roles_y_refactor.md). Tras cerrar la extensión de Fase 1 (CRUD Roles + refactor de nombres) se arrancó Fase 2 con un **planning estratégico previo** y se completó la primera de las 6 iteraciones previstas.

---

## 🎯 Decisión clave: móvil primero

Antes de empezar a codificar se discutieron 9 decisiones de modelo. La más importante fue **invertir el orden tradicional**: en lugar de hacer primero el CRUD web del admin (más fácil técnicamente) y luego adaptar al móvil, se decide hacer **móvil primero**.

### Razones

1. **El albarán nace en obra** (móvil del trabajador), no en la oficina. El admin es la SEGUNDA parada del dato.
2. **El móvil es el caso restrictivo**: menos campos, pantalla pequeña, dedos gordos. Diseñar primero ahí obliga a quedarse con lo esencial. Es más fácil **empezar pequeño y crecer** que empezar grande y podar.
3. **La firma — pieza legal central — vive en el móvil**. Resolverla en la primera o segunda iteración, no semanas después.
4. **Toda la infraestructura móvil de Fase 2 se reutiliza en Fases 3, 4 y 5** (ausencias, incidencias, resúmenes mensuales del trabajador).
5. **Sin albaranes creados desde móvil, el CRUD web del admin sería un mueble vacío**.

### Consecuencia organizativa

Se reorganiza Fase 2 en **6 iteraciones** (en lugar de 5) para invertir en infraestructura móvil al principio. Esa inversión se amortiza en 4 fases.

```
Iter. 1 — Núcleo de datos                    ✅ COMPLETADA
Iter. 2 — Infraestructura móvil (siguiente)  ⏳
Iter. 3 — CRUD albarán desde móvil           ⏳
Iter. 4 — Firma + flujo legal                ⏳
Iter. 5 — CRUD web del admin                 ⏳
Iter. 6 — Refinamiento + adjuntos            ⏳
```

---

## 📋 Las 9 decisiones cerradas en planning

| # | Pregunta | Decisión |
|---|---|---|
| 1 | ¿Web o móvil primero? | **Móvil primero** |
| 2 | ¿Cuántos tipos de hora? | **4**: `laborable_normal`, `laborable_extra`, `festivo_normal`, `festivo_extra` |
| 3 | ¿Cuándo descuenta stock? | **Al crear/editar la línea de material** (con Observer) |
| 4 | Estados del albarán | `borrador → pendiente_firma → firmado → facturado → archivado` con bifurcaciones a `archivado` desde cualquier punto |
| 5 | ¿Token single-use? | **Sí + caducidad por tiempo** |
| 6 | ¿Quién firma como responsable? | **Cualquiera con el token email**. El albarán tiene **2 huecos** (trabajador + responsable). El token sabe a qué hueco va dirigido. |
| 7 | Geolocalización al firmar | **Opcional** con prompt del navegador |
| 8 | Modal "Solicitar firma" desde web | **Selector** con 3 opciones: trabajador / responsable del proyecto / email custom |
| 9 | Borrar firmas | **Como imágenes**: si hay error, se borra el registro. Activity log automático para auditoría. Sin pedir nombre ni DNI en pantalla pública. |

---

## 🛠️ Lo que se completó en esta iteración (1)

### 🗄️ Migración con 5 tablas + relaciones

[`2026_05_14_120000_create_albaranes_tables.php`](../../database/migrations/2026_05_14_120000_create_albaranes_tables.php) crea:

- **`albaranes`** (cabecera): numero (único), fecha, FKs a cliente/proyecto/creador/responsable, estado (enum), observaciones, snapshot_data (json), softDeletes.
- **`albaran_lineas_personal`**: trabajador + tipo_hora + horas (decimal 5,2) + observaciones.
- **`albaran_lineas_material`**: lote + cantidad (decimal 10,2) + observaciones.
- **`albaran_firmas`** (máx 2 por albarán, 1 por tipo): tipo, firmado_por_user_id o token_id, firma_path (PNG), ip, user_agent, geolocalizacion (json), firmado_at. Constraint único `(albaran_id, tipo)`.
- **`albaran_tokens_firma`**: tipo_firmante, token (único), email_destino, nombre_destino, caduca_at, usado_at, invalidado_at, reemplazado_por_token_id (autoreferencia), generado_por_user_id.

Todas las FKs con `cascadeOnDelete` o `restrictOnDelete` según semántica.

### 🎭 Enums tipo-safe

- **`App\Enums\EstadoAlbaran`** con `transicionesPermitidas()`, `puedeTransicionarA()`, `esEditable()`, `bloqueaEdicion()`, `etiqueta()`, `tono()` (para badges).
- **`App\Enums\TipoHora`** con `etiqueta()`, `esExtra()`, `esFestivo()`.
- **`App\Enums\TipoFirma`** con `etiqueta()`.

### 📐 5 Modelos Eloquent

- **`Albaran`**: 8 relaciones (cliente, proyecto, creador, responsable, lineasPersonal, lineasMaterial, firmas, tokensFirma) + helper `tieneFirma(string $tipo)`.
- **`AlbaranLineaPersonal`**: cast a `TipoHora`.
- **`AlbaranLineaMaterial`**: cast `cantidad` decimal:2.
- **`AlbaranFirma`**: cast a `TipoFirma` + `geolocalizacion` array.
- **`AlbaranTokenFirma`**: helper `esValido()` (no usado + no invalidado + no caducado), relación inversa con la firma generada.

### ⚙️ Observer de stock con consistencia automática

[`AlbaranLineaMaterialObserver`](../../app/Observers/AlbaranLineaMaterialObserver.php) reacciona a tres eventos:

| Evento | Comportamiento |
|---|---|
| `created` | Descontar `cantidad` del lote |
| `updated` | Si misma lote: ajustar diff (positivo o negativo). Si cambio de lote: devolver al viejo + descontar del nuevo |
| `deleted` | Devolver `cantidad` al lote |

Usa `lockForUpdate` + `DB::transaction` para garantizar consistencia bajo concurrencia. Registrado en `AppServiceProvider::boot()`.

### 🔢 NumeracionService — números de albarán configurables

[`NumeracionService`](../../app/Services/NumeracionService.php) lee la plantilla de `empresa.plantilla_numeracion_albaran` (con fallback `ALB-{YYYY}-{NNNN}`).

**Variables soportadas**: `{YYYY}` (año 4 dígitos), `{YY}` (año 2 dígitos), `{MM}` (mes), `{NNNN}` (secuencial 4 dígitos), `{NNN}` (3 dígitos), `{NN}` (2 dígitos).

**Lógica del secuencial**:
- Cuenta los albaranes existentes en el año de la fecha (incluyendo soft-deleted para no reusar números).
- Aplica `lockForUpdate` para evitar colisiones bajo carga concurrente.
- Suma 1.

Ejemplo: con plantilla `PT-{YYYY}/{MM}/{NNN}` y fecha 10/03/2026 → `PT-2026/03/001`.

### 🏭 Factories + Fase2DemoSeeder

- 5 factories con states convenientes: `pendienteFirma()`, `firmado()`, `facturado()`, `archivado()` en `AlbaranFactory`; `caducado()`, `usado()`, `invalidado()` en `AlbaranTokenFirmaFactory`.
- **`Fase2DemoSeeder`** crea 5 albaranes con líneas, firmas y tokens en estados distintos: 1 borrador · 1 pendiente_firma (con token vigente al responsable + firma trabajador hecha) · 2 firmados (con ambas firmas) · 1 facturado.

### 🔐 4 permisos nuevos en el seeder

| Permiso | Ámbito | Asignado a |
|---|---|---|
| `albaranes.descargar_pdf` | ambos | superadmin, administrador, trabajador, responsable |
| `albaranes.solicitar_firma` | web | superadmin, administrador |
| `albaranes.invalidar_firma` | web | **solo superadmin** (auditoría legal) |
| `albaranes.facturar` | web | superadmin, administrador |

### ✅ 19 tests nuevos

- **`EstadoAlbaranTest`** (7 unitarios): transiciones permitidas/bloqueadas, terminal en archivado, editabilidad solo en borrador.
- **`NumeracionServiceTest`** (6 feature): plantilla por defecto, resolución de variables, plantilla de empresa, secuencial por año, independencia entre años, soft-deleted contando para evitar reuso.
- **`AlbaranLineaMaterialObserverTest`** (6 feature): descuento al crear, ajuste en aumento/reducción, devolución al eliminar, cambio de lote, eliminación masiva.

---

## 📊 Métricas finales

```
Pint:             ✅ passed
Larastan:         ✅ 51/51 sin errores
PHPUnit:          ✅ 145 passed · 502 assertions · ~41 s
migrate:fresh:    ✅ OK con seeders demo de Fase 1 + Fase 2

Migraciones:                12 (11 anteriores + 1 nueva de albaranes)
Modelos Eloquent:           17 (12 anteriores + 5 nuevos de Fase 2)
Enums:                       3 (EstadoAlbaran, TipoHora, TipoFirma)
Servicios:                   1 (NumeracionService)
Observers:                   1 (AlbaranLineaMaterialObserver)
Factories:                   5 nuevas
Seeders:                     5 (RolesAndPermissionsSeeder + AdminUsersSeeder + 2 Fase1 + Fase2DemoSeeder)
Permisos catalogados:       58 (54 anteriores + 4 nuevos de albaranes)
Tests:                     145 (126 anteriores + 19 nuevos)
```

---

## ⏳ Lo que falta hacer en Fase 2

### Iter. 2 — Infraestructura móvil (siguiente)
- Layout `mobile.blade.php` con header + nav inferior optimizada táctil.
- Rutas `/movil/...` con middleware `EnsureMobileAccess`.
- Dashboard del trabajador (lista "Mis albaranes recientes" + atajos a "Nuevo parte" / "Ausencias" / "Mi resumen").
- Componentes Blade UI específicos móvil: `<x-mobile.button>`, `<x-mobile.card>`, `<x-mobile.list-item>`, etc. (set mínimo, se amplía bajo demanda).
- Pantalla de login móvil si hace falta.

### Iter. 3 — CRUD albarán desde móvil
- Pantalla "Nuevo Parte de Trabajo" simplificada y optimizada táctil.
- Selects dependientes táctiles: proyecto → trabajadores del proyecto → materiales/lotes del proyecto.
- Sección líneas de personal (con compañeros + horas + tipo).
- Sección líneas de material (con cantidad y descuento de stock en vivo gracias al Observer).
- Guardar como borrador / editar / eliminar mis borradores.

### Iter. 4 — Firma + flujo legal **(LA PIEZA CENTRAL)**
- Componente firma Canvas + Alpine, guarda PNG en storage.
- Doble firma presencial in-situ (trabajador → responsable presente).
- Generación de token email para responsable ausente.
- Ruta pública `/firmar/{token}` SIN auth con vista minimal (info básica del albarán + canvas + botón firmar).
- Transiciones automáticas de estado cuando ambos huecos están llenos.
- Geolocalización opcional con prompt del navegador.
- Activity log de todas las acciones críticas.
- Mailable `AlbaranListoFirmaMail` para el responsable.
- Generación PDF con mPDF (plantilla configurable).

### Iter. 5 — CRUD web del admin
*(ahora sí hay albaranes que gestionar)*
- Pantalla `/albaranes` con tabla + filtros + modal alta/edición (más campos que el móvil).
- Cambio de firmantes asignados desde web (`creado_por`, `responsable_id`) cuando el hueco está vacío.
- Modal "Solicitar firma" con selector (trabajador / responsable del proyecto / email custom).
- Gestión de tokens: reenviar, regenerar, invalidar.
- Eliminar firmas con permiso `albaranes.invalidar_firma` + activity log.
- Forzar transiciones de estado con permiso `albaranes.modificar_terminado`.
- Botón "Descargar PDF" desde la tabla.

### Iter. 6 — Refinamiento + adjuntos
- Adjuntos múltiples (PDFs externos, fotos) vía `spatie/laravel-medialibrary`.
- Snapshot de datos al firmar (foto histórica inmutable de cliente, proyecto, líneas).
- Tests adicionales para edge cases y flujos completos end-to-end.

---

## 🎨 Decisiones de diseño confirmadas en esta iteración

27. **Móvil primero**: invertir el orden tradicional. Toda la infraestructura móvil de Fase 2 se reutiliza en Fases 3, 4 y 5.
28. **Stock descuenta automáticamente vía Observer** en la línea de material. El estado del stock siempre refleja la realidad de la obra. Si el albarán se elimina (cascade en BD), las líneas no disparan el Observer — el albarán se elimina con `$albaran->lineasMaterial()->each(fn ($l) => $l->delete())` para garantizar el rollback.
29. **2 huecos de firma en cada albarán** + el token determina qué hueco rellena. Da igual quién firme físicamente: la auditoría queda en `albaran_firmas.token_id` + `albaran_firmas.ip` + `albaran_firmas.firmado_at`.
30. **NumeracionService es transaccional con lockForUpdate** para evitar números duplicados bajo concurrencia. La plantilla `{YYYY}-{NNNN}` es la convención inicial, configurable por cliente desde "Configuración Empresa".
31. **Permiso `albaranes.invalidar_firma` solo superadmin**: borrar firmas es delicado legalmente; queda en activity log para auditoría.

---

## 🛣 Hoja de ruta inmediata

```
🚧 FASE 2 ── 17 % ──────────────────────────────────────
   ✅ Iter. 1 — Núcleo de datos
   ⏳ Iter. 2 — Infraestructura móvil
   ⏳ Iter. 3 — CRUD móvil del albarán
   ⏳ Iter. 4 — Firma + flujo legal (la pieza central)
   ⏳ Iter. 5 — CRUD web del admin
   ⏳ Iter. 6 — Refinamiento + adjuntos
─────────────────────────────────────────────────────────
   ⏭️ FASE 3 — Albarán personalizado (2-3 sem)
   ⏭️ FASE 4 — Ausencias e incidencias (2-3 sem)
   ⏭️ FASE 5 — Reportes y exportación (2 sem)
```

---

## 📝 Notas finales

- **El móvil aún NO es accesible**: la infraestructura móvil se construirá en la Iter. 2. Por ahora solo existe la BD, los modelos, el Observer y el servicio de numeración.
- Los albaranes demo creados por `Fase2DemoSeeder` ya se pueden inspeccionar directamente en la BD (`SELECT * FROM albaranes`), pero todavía no hay UI para verlos.
- **La pieza con más riesgo de Fase 2 es la firma** (Iter. 4): Canvas táctil, geolocalización, tokens públicos, generación PDF. Conviene reservar tiempo extra cuando lleguemos.
- **`spatie/laravel-medialibrary`** se introducirá en Iter. 6 para adjuntos. Hasta entonces, los logos se siguen guardando con `Storage::disk('public')` directo (decisión tomada en Fase 1).

---

**Iteración 1 cerrada. Siguiente: arrancar Iter. 2 (infraestructura móvil) — primer paso real hacia el albarán táctil.**
