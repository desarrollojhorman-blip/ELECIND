# V2 — Tarifas + Partes · Estado al 16/06/2026

Documento operativo del avance de la **Versión 2** tras el cierre de las **Fases 1, 2, 3 (Tarifas)** y la **Fase 4 (Partes)**.

Recoge:

1. Qué está implementado (bloque a bloque, con los archivos tocados).
2. Decisiones tomadas durante la implementación que se desviaron del plan original.
3. Lo que falta por hacer (Fases 5-8 + Fase 1.5 condicional).
4. "Gotchas" técnicos a recordar.
5. Cómo probar lo que está vivo.

Referencias:
- Documento de arranque: [`150626_inicio.md`](./150626_inicio.md) — plan de fases y modelo de datos.
- Análisis del Excel del cliente: [`docs/excel/tarifas/resumen_seguimiento.md`](../../../excel/tarifas/resumen_seguimiento.md).

---

## 1 · Bloque Tarifas (Fases 1, 2, 3)

### 1.1 · Base de datos

**Tablas nuevas:**

| Tabla | Filas / Propósito |
|---|---|
| `atributos_hora` | Catálogo fijo (11 filas — Labor, Lab Noche, Fest, Fest Noct, 4 extras, 3 pluses). Sembrado por `AtributosHoraSeeder`. |
| `tarifas_cliente` | Tarifa que se cobra al cliente por `(cliente, tipo_proyecto, atributo)`. UNIQUE compuesto. |
| `tarifas_historial` | Auditoría unificada (cliente + trabajador). El campo `tipo` distingue origen, `referencia_id` apunta a `tarifas_cliente.id` o `users.id`. |

**Tablas existentes ampliadas:**

| Tabla | Cambio |
|---|---|
| `users` | +5 tasas: `tasa_lab_noche`, `tasa_fest_noche`, `tasa_ex_lab_noc`, `tasa_ex_fes`, `tasa_ex_fes_noct`. Las 3 existentes (`tasa_hora`, `tasa_extra`, `tasa_festivo`) se mantuvieron y se hicieron `NOT NULL DEFAULT 0`. Total: 8 tasas. |
| `tipos_proyectos` | +`genera_albaran_por_defecto` (bool, default true). Lo lee el `ParteObserver` para autoflag de partes nuevos. |

**Migraciones (5 archivos):**
- `2026_06_15_100000_create_atributos_hora_table.php`
- `2026_06_15_100100_add_genera_albaran_por_defecto_to_tipos_proyectos.php`
- `2026_06_15_100200_add_tasas_v2_to_users.php` (incluye backfill NULL → 0)
- `2026_06_15_100300_create_tarifas_cliente_table.php`
- `2026_06_15_100400_create_tarifas_historial_table.php`

### 1.2 · Modelos Eloquent

| Modelo | Notas |
|---|---|
| `App\Models\AtributoHora` | Constantes `COD_*` y `GRUPO_*`. Scopes `normales()`, `extras()`, `pluses()`, `horas()`. `porMapeoTasa($campo)` para el observer de users. |
| `App\Models\TarifaCliente` | FK a cliente, tipo_proyecto y atributo. |
| `App\Models\TarifaHistorial` | Scopes `clientes()` y `trabajadores()`. Constantes `TIPO_CLIENTE` y `TIPO_TRABAJADOR`. |

### 1.3 · Observers

| Observer | Qué hace |
|---|---|
| `App\Observers\TarifaClienteObserver` | Al `created`/`updated` con `importe` dirty → escribe fila en `tarifas_historial` con `tipo='cliente'`. |
| `App\Observers\UserTasasObserver` | Al `updated` de un User → registra cada `tasa_*` cambiada en historial con `tipo='trabajador'`. Cachea `atributos_hora` por mapeo. |

Registrados en `AppServiceProvider::boot()`.

### 1.4 · UI — módulo `Tarifas`

3 pantallas (ruta + componente Livewire + vista):

| Ruta | Componente | Función |
|---|---|---|
| `/tarifas/clientes` | `Tarifas\Clientes\Index` | Cross-join cliente activo × tipo_proyecto activo. Modo lectura por defecto + botón ✏️ Editar por fila. Filas autogeneradas, sin "crear". Filtros: buscar (incluye `codigo_cliente`), cliente, tipo_proyecto. Paginación + orden + selector "Filas:" arriba. Modal historial contextual. |
| `/tarifas/trabajadores` | `Tarifas\Trabajadores\Index` | Una fila por user **interno activo** (excluye roles con `es_externo`) + jerarquía por nivel (no se ven niveles superiores al propio). 8 columnas editables. Soporta `?usuario={id}` para llegar desde la ficha. Filtros: buscar + rol. |
| `/tarifas/historial` | `Tarifas\Historial\Index` | Vista global de `tarifas_historial`. Filtros: buscar (cliente/trabajador), tipo, atributo, usuario que cambió, rango de fechas. Solo lectura. |

Permisos sembrados (`RolesAndPermissionsSeeder`):
- `tarifas.ver` · `tarifas.editar_clientes` · `tarifas.editar_trabajadores` · `tarifas.historial_ver`
- Asignados a **superadmin** y **administrador** automáticamente.

### 1.5 · Integración con fichas existentes

**Ficha de Cliente** (`Editar` y `Ver`):
- Nueva pestaña **"Tarifas"** entre "Cliente" y "Albaranes".
- Componente embebido: `App\Livewire\Tarifas\Clientes\Bloque` (vista `livewire.tarifas.clientes.bloque`).
- En `Editar`: edición inline por fila.
- En `Ver`: el mismo componente con `soloLectura=true`.

**Ficha de Usuario** (`Editar` y `Ver`):
- Nueva pestaña **"Tarifas"** entre "Usuario" y "Albaranes".
- Visible SOLO si el usuario es **interno** (su rol no tiene `es_externo`).
- En `Editar`: 8 inputs vinculados a `form.tasa_*`.
- En `Ver`: 8 valores en modo lectura.
- **Caso "listillo" blindado**: si rellenas las tarifas y cambias el rol a externo antes de guardar, `UserForm::save()` fuerza las 8 a 0.

### 1.6 · Mejoras de UX aplicadas (transversales)

| Cambio | Dónde |
|---|---|
| Mini-card contextual a la derecha del título | Editar/Ver de Cliente, Usuario, Pedido, Material, Proyecto, Albarán |
| Cabeceras ordenables con `<x-ui.sortable-header>` | Las 3 pantallas de Tarifas |
| Selector "Filas:" arriba de la tabla (estilo Clientes) | Todas las pantallas de Tarifas |
| Modo lectura por defecto + botón ✏️ Editar por fila | Tarifas → Clientes y Trabajadores |
| Formato sin ceros sobrantes (`0` en vez de `0,0000`) | Todos los importes y tasas |
| Mini-card con mismo tamaño tipográfico que el título | Las 6 secciones con ficha (xl arriba, sm abajo) |

---

## 2 · Bloque Partes (Fase 4)

### 2.1 · Base de datos

**2 tablas nuevas:**

| Tabla | Propósito |
|---|---|
| `partes` | Cabecera del parte. `codigo` UNIQUE (`PT-YYYY-NNNN`), `user_id` (operario), `proyecto_id`, `fecha`, `hora_inicio`/`hora_fin`, `es_albaran`, `albaran_id` (nullable, lo rellena Fase 5), `observaciones`, `estado` (enum `abierto`/`cerrado`). 7 columnas de snapshots de cabecera + soft deletes. |
| `partes_lineas_personal` | Una fila por `(parte, trabajador, atributo)` — modelo **long**. UNIQUE compuesto. `cantidad` decimal(6,2). 8 columnas de snapshots (trabajador, atributo, tarifa, tasa, facturación, coste). |

**Migraciones:**
- `2026_06_15_110000_create_partes_table.php`
- `2026_06_15_110100_create_partes_lineas_personal_table.php`

### 2.2 · Modelos Eloquent

| Modelo | Notas |
|---|---|
| `App\Models\Parte` | Constantes `ESTADO_ABIERTO`/`ESTADO_CERRADO`. Scopes `abiertos()`, `cerrados()`, `deOperario($id)`. Accesors: `horasTotales()`, `facturacionTotal()`, `costeTotal()`, `margenTotal()`, `esEditable()`. LogsActivity registrado. |
| `App\Models\ParteLineaPersonal` | Casts decimales. Relaciones `parte()`, `trabajador()`, `atributo()`. Accesor `margen()`. |

### 2.3 · Observers

| Observer | Qué hace |
|---|---|
| `App\Observers\ParteObserver` | `creating`: genera código `PT-YYYY-NNNN` secuencial por año + autoflag `es_albaran` desde `tipo_proyecto.genera_albaran_por_defecto` si no se ha tocado manualmente. `saving`: snapshots de operario (apellidos+nombre) y de proyecto + cliente + tipo_proyecto cuando cambian las FK. |
| `App\Observers\ParteLineaPersonalObserver` | `saving`: snapshots de trabajador y atributo + **recalculo de los 4 snapshots económicos** si cambia `cantidad`, `user_id` o `atributo_id`. `tarifa_snapshot` se busca en `tarifas_cliente` por `(cliente_id_snapshot, tipo_proyecto_id_snapshot, atributo_id)`. `tasa_snapshot` sale de `users.{mapeo_tasa}` según el atributo. |

Ambos registrados en `AppServiceProvider::boot()`.

### 2.4 · Policy

`App\Policies\PartePolicy` — mismo espíritu que `AlbaranPolicy`:
- `viewAny`: combinación de `partes.ver_todos` y `partes.ver_propios`.
- `view`: si `ver_propios`, lo ve si es el creador o si aparece en alguna línea.
- `update`/`delete`: el creador siempre puede mientras esté **abierto**. Para tocar partes de otros se necesita `partes.modificar`/`partes.eliminar`. Bloqueo automático si `estado=cerrado`.

Registrado en `AppServiceProvider::boot()` con `Gate::policy()`.

### 2.5 · Permisos

7 permisos nuevos (`RolesAndPermissionsSeeder`):
- `partes.ver_todos` · `partes.ver_propios`
- `partes.crear_web` · `partes.crear_movil`
- `partes.modificar` · `partes.eliminar` · `partes.exportar`

Asignaciones:
- **superadmin**: todos.
- **administrador**: todos.
- **jefe_de_equipo**: `ver_todos`, `crear_web`, `modificar`, `exportar`.
- **trabajador**: `ver_propios`, `crear_movil` (para Fase 6).

### 2.6 · Sidebar reorganizado

Antes:
```
Albaranes/
  ├─ Borradores
  └─ Albaranes
```

Ahora:
```
Partes/
  ├─ Partes (nuevo)
  ├─ Albaranes (existente)
  └─ Borradores (existente)
```

### 2.7 · UI — módulo `Partes`

4 componentes Livewire + 3 vistas Blade:

| Ruta | Componente | Función |
|---|---|---|
| `/partes` | `Partes\Index` | Listado con 8 filtros (buscar, operario, proyecto, cliente, estado, es_albaran, desde, hasta), búsqueda por código/observaciones/snapshots, paginación, orden por columnas, eliminación inline con confirmación. |
| `/partes/crear` | `Partes\Editar` (mismo componente para crear/editar) | Form con `ParteForm`. Preselecciona al user logueado si es interno. Al cambiar `proyecto_id` autorrelena `es_albaran` desde `tipo_proyecto`. Pestaña "Líneas" bloqueada hasta el primer guardado. |
| `/partes/{parte}/editar` | `Partes\Editar` | Tabs **Parte** + **Líneas**. Tabla wide de líneas inline (trabajador, atributo, cantidad, motivo). Add/remove de líneas en vivo. |
| `/partes/{parte}` | `Partes\Ver` | Tabs **Parte** + **Líneas** en lectura. Bloque de totales (horas, facturación, coste, margen). Modal de eliminación con confirmación. |

**Form Object** `App\Livewire\Forms\ParteForm`:
- Validación de cabecera (`required` operario/proyecto/fecha; `date_format:H:i` para horas).
- Array `lineasPersonal[]` con sincronización (upsert + borrado de las quitadas) en una transacción.
- Filas vacías (sin trabajador o sin atributo) se skipean al guardar.

---

## 3 · Decisiones que se desviaron del plan original

| Plan inicial (150626_inicio.md) | Lo que se implementó | Motivo |
|---|---|---|
| Tabla `partes_lineas_personal` con columnas `cantidad` solo | Misma + `motivo_ajuste` (string nullable) | Necesario para el caso "Juan se fue al médico" sin requerir módulo aparte. |
| Modo "crear nueva tarifa" manual | Cross-join automático (todas las combinaciones cliente×tipo_proyecto activos visibles, edición inline) | Petición del cliente para no obligar a crear filas. |
| Bloque de Tarifas embebido en el form de Usuario | Pestaña "Tarifas" separada dentro de la ficha | Petición del cliente: opción B real, no botón externo. |
| Permiso `usuarios.gestionar_tarifas` (existente) | Se mantiene + se añaden `tarifas.*` (4 permisos nuevos) | Ambos cubren accesos distintos (legacy + módulo nuevo). |
| Modelo wide de líneas (`horas` + `horas_extra`) | Modelo long confirmado (una fila por atributo) | Coincide con cómo el Excel del cliente lo maneja en `INPUT`. |
| Numeración manual de partes | Autocódigo `PT-YYYY-NNNN` por año en el Observer | Reutilización del patrón de albaranes. |

---

## 4 · Lo que falta por hacer

### 4.1 · Fase 1.5 — Bloqueada por respuesta del cliente

**Coste al trabajador de los pluses** (PLUS RETÉN, PLUS FESTIVO, PLUS NOCHE):

Hay que confirmar con el cliente:
- ¿Es **constante** por tipo de plus (60 € PLUS RETÉN, 16 € PLUS FESTIVO) → va en `atributos_hora.coste_trabajador_flat`?
- ¿Varía **por cliente** → va en `tarifas_cliente` con columna nueva `coste_trabajador`?
- ¿Varía **por trabajador** → nueva tabla `pluses_trabajador`?

Hasta que responda, los 3 pluses están en el catálogo sin coste calculable. En partes, el `tasa_snapshot` de un plus se queda en 0.

### 4.2 · Fase 5 — Vínculo Parte ↔ Albarán

- Listener: al **firmar un albarán** se crea/vincula automáticamente su parte (`partes.albaran_id`).
- Script de **backfill** para albaranes históricos: por cada albarán existente, generar el parte espejo con sus líneas a partir de `albaran_lineas_personal`.
- UI: en `Partes/Ver` mostrar enlace al albarán si está vinculado; en `Albaranes/Ver` enlace al parte.

### 4.3 · Fase 6 — App móvil

- Endpoint API `POST /api/partes` con flag `es_albaran` en el payload.
- App móvil: pantalla "Crear nuevo" con dos opciones:
  - "Crear albarán (con firma)" — flujo actual.
  - "Crear parte (sin firma)" — flujo nuevo con aviso modal.
- El default viene de `tipo_proyecto.genera_albaran_por_defecto`.

### 4.4 · Fase 7 — Recálculo masivo de tarifas

- Pantalla en `Partes/` con filtros (fecha, cliente, tipo_proyecto, atributo).
- Preview "Se actualizarán X partes y Y líneas de albarán abierto".
- Ejecución en transacción atómica. Firmados intocables.

### 4.5 · Fase 8 — Reportes

- **Mix de horas** (réplica del pivot del Excel del cliente).
- **Margen por proyecto** / por cliente / por trabajador.
- Exportable a Excel.

### 4.6 · Mejoras menores pendientes en lo ya implementado

- En **Partes/Index**, añadir columna de horas totales (computed en runtime — agregada desde líneas).
- En **Partes/Editar**, mostrar feedback en vivo del importe estimado al introducir cantidad (sin tocar BD).
- En **Tarifas/Historial**, posibilidad de exportar a Excel.
- **Catálogo de festivos** (pospuesto en el plan original): cuando se haga, alimentará un selector visual en Partes/Editar.

---

## 5 · Gotchas técnicos a recordar

### 5.1 · Snapshots e inmutabilidad

- Todos los snapshots (tarifas, tasas, nombres, códigos) se rellenan en `saving` solo cuando la FK correspondiente está `isDirty`. Si solo se cambia "observaciones" no se sobrescriben.
- **Cuando el parte pase a `estado=cerrado`** (Fase 5, al firmar el albarán), debe bloquearse cualquier mutación de líneas. La `PartePolicy::update` ya lo hace mirando `esEditable()`.

### 5.2 · Cálculo de tarifa en líneas

`ParteLineaPersonalObserver::buscarTarifa()` lee de `tarifas_cliente` con:
```
cliente_id  = parte.cliente_id_snapshot
tipo_proy.  = parte.tipo_proyecto_id_snapshot
atributo    = línea.atributo_id
```

Si no encuentra fila → tarifa 0. **Esto significa que el orden importa**: el snapshot del parte (cliente/tipo_proyecto) debe estar persistido antes de guardar las líneas. El `ParteForm::save()` ya lo respeta (guarda cabecera primero, luego líneas).

### 5.3 · Plus al trabajador

Los atributos del grupo "plus" tienen `mapeo_tasa=NULL` en el catálogo. Eso provoca que `tasa_snapshot=0` en líneas con plus. Cuando se cierre la Fase 1.5 habrá que ampliar `ParteLineaPersonalObserver::buscarTasa()` para resolver el coste del plus por el camino correcto (catálogo global, por cliente o por trabajador).

### 5.4 · Caso "listillo"

`UserForm::save()` fuerza las 8 tasas a 0 si el rol final es externo. Esto:
- Bloquea que se rellenen tasas y se cambie a externo antes de guardar.
- **Resetea automáticamente** tasas previas si un interno pasa a externo. Esto deja constancia en `tarifas_historial` vía el `UserTasasObserver` (cada tasa anterior → 0). Buena auditoría, pero ojo si el cliente cambia a alguien de rol "por error".

### 5.5 · Activity Log vs Tarifas Historial

Coexisten dos sistemas de auditoría:
- `activity_log` (Spatie LogsActivity) — auditoría genérica del CRUD.
- `tarifas_historial` — auditoría específica de cambios económicos.

**No solapar info**: la columna `logOnly` del modelo `User` NO incluye las 8 tasas (las gestiona `UserTasasObserver`). El modelo `TarifaCliente` no tiene `LogsActivity` activo (lo gestiona `TarifaClienteObserver`). Mantener así.

### 5.6 · Migraciones aditivas en producción

Recordatorio del documento de v1: **producción está vivo**. Toda migración futura debe ser **solo añadir columnas/tablas/FK** o **modificar con backfill seguro**. Nunca `drop` ni `rename` sin script de compatibilidad.

### 5.7 · `es_albaran` y el autoflag

- En el **Observer**: aplica el default solo si `! isDirty('es_albaran')` Y hay proyecto.
- En el **componente Editar**: `updatedFormProyectoId()` reescribe el flag explícitamente cada vez que cambia el proyecto. Esto puede sobrescribir una elección manual del usuario si después cambia el proyecto. **A vigilar**: posible mejora futura — un mini modal "¿Reescribir el flag?".

### 5.8 · Líneas con cantidad = 0

El formulario **descarta líneas sin trabajador o sin atributo** al guardar. Pero **acepta cantidad 0**. Posible regla de negocio futura: descartar también las de cantidad 0, o marcarlas como "presente pero sin imputación".

---

## 6 · Cómo probar lo que está vivo

### Tarifas

```
/tarifas/clientes       → matriz wide de tarifas, edita inline
/tarifas/trabajadores   → 8 tasas por trabajador interno activo
/tarifas/historial      → auditoría unificada con 6 filtros
```

Smoke:
1. Edita una tarifa de EXIDE en MANTENIMIENTO → comprobar que aparece en Historial.
2. Edita una tasa de un trabajador desde Tarifas → comprobar que aparece en Historial.
3. Edita la misma tasa desde la ficha del usuario (pestaña Tarifas) → comprobar consistencia.

### Partes

```
/partes                 → listado con filtros
/partes/crear           → form completo + transición a "/partes/{id}/editar" tras guardar
/partes/{id}/editar     → tabs Parte + Líneas
/partes/{id}            → lectura con totales
```

Smoke:
1. Crear un parte para `proyecto = un MANTENIMIENTO`. Verificar que `es_albaran` se autoflagea según `tipo_proyecto.genera_albaran_por_defecto`.
2. Tras guardar, en la pestaña Líneas añadir 2 trabajadores con `Labor 8h` y `Ex Lab 2h`. Verificar snapshots económicos al guardar.
3. Ir a `/partes/{id}` y comprobar **horas totales = 10**, facturación y coste calculados.

---

## 7 · Estado global de la v2

| Bloque | Estado |
|---|---|
| Fase 1 (Base estructural) | ✅ Cerrada |
| Fase 2 (Catálogo de tarifas cliente) | ✅ Cerrada (incluida en Fase 1) |
| Fase 3 (Coste flat de pluses al trabajador) | ⏳ Bloqueada por respuesta del cliente |
| Fase 4 (Modelo Partes) | ✅ Cerrada (16/06/2026) |
| Fase 5 (Vínculo Parte ↔ Albarán) | ⏳ Siguiente |
| Fase 6 (App móvil) | ⏳ Pendiente |
| Fase 7 (Recálculo masivo de tarifas) | ⏳ Pendiente |
| Fase 8 (Reportes Mix Horas + Margen) | ⏳ Pendiente |

---

## 8 · Preguntas abiertas al cliente

Sin variación respecto al documento de inicio. Reproducidas aquí para conveniencia:

1. **Pago al trabajador — interpretación A vs B**: confirmado A (cada hora a su tasa) para el cálculo interno. El cliente confirmó que él lo cuadra con su gestor; nuestro modelo da el coste interno para el margen.
2. **Mapeo exacto `Ex Lab Noc` → tasa**: pendiente confirmar.
3. **Pluses — coste al trabajador**: ¿constante, por cliente, por trabajador? **Esto bloquea Fase 1.5**.
4. **Festivos locales**: ¿qué localidades? (La Solana y Manzanares vistas en el Excel).
5. **¿Atributos nuevos a futuro?**: si sí, evaluar si el catálogo fijo basta o conviene UI de admin.

---

## 9 · Estado del documento

- **Cerrado el 16/06/2026** tras completar Fase 4.
- **Próximo paso**: Fase 5 (vínculo parte↔albarán + backfill).
- Cualquier cambio posterior se anota en un nuevo archivo con nombre `DDMMYY_*.md` en esta misma carpeta. **Este documento no se reescribe**.
