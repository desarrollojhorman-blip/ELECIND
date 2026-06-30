# Resumen de Sesión — 2026-05-21 (tarde) · Tasas en Usuarios + Snapshots de Albarán

Fecha: 2026-05-21 ~15:00
Estado: Estructura completa para congelar datos en albaranes implementada.
Pendiente antes de seguir con albaranes: **repaso global de los módulos base**.

---

## 1. Contexto

Esta sesión cubre dos bloques relacionados:

1. **Tasas (€/hora) por Usuario** — campo nuevo en `users` para guardar la tarifa
   por hora de cada trabajador (base, extra, festivo), con su permiso de control.
2. **Snapshots en albaranes** — toda la infraestructura para "congelar" datos
   históricos de cliente/proyecto/concepto/creador/responsable/trabajador/material
   en el momento del albarán, de modo que cambios posteriores en esas entidades
   NO alteren los albaranes ya creados.

> **Importante:** los snapshots se rellenan automáticamente desde **ahora**.
> Los formularios de creación/edición de albarán **aún no muestran** los
> campos snapshot como inputs editables — eso queda para la próxima iteración
> después del repaso global de módulos.

---

## 2. Tasas en Usuario (Fase 1)

### Migración
- `2026_05_21_100000_add_tasas_to_users` → 3 columnas `DECIMAL(8,3) NULL` en `users`:
  - `tasa_hora`, `tasa_extra`, `tasa_festivo`.
- Aplican principalmente a trabajadores internos; opcionales para todos.

### Permiso nuevo
- **`usuarios.gestionar_tarifas`** (web, categoría usuarios).
- Excluido del rol `administrador` → por defecto solo superadmin.
- Otorgable a cualquier rol desde la pantalla de Roles sin tocar código.

### Cambios en código
- `User`: añadidas las 3 columnas a `fillable`.
- `UserFields`: plantilla `'tasa'` + 3 entradas en `config()`.
- `UserForm`: 3 propiedades, `fromModel()`, `save()` con parseo `,`→`.`
- `Usuarios\Editar` / `Usuarios\Ver` (páginas dedicadas): nueva subsección
  **"Tasas (€/hora)"** debajo de Datos personales, con labels `Tasa base €/h`,
  `Tasa extra €/h`, `Tasa festivo €/h`. Visible solo con permiso.
- El checkbox "Usuario activo" se movió **al final** (después de Tasas),
  con separador `border-t`.
- **Importar/Exportar Usuarios**: 3 columnas opcionales de tasas, condicionales
  por permiso (si no tienes `gestionar_tarifas`, los campos no aparecen en el
  mapeo ni en el PDF/Excel).

### UI
- Listado: subtítulo cambiado a *"N usuarios · activos e inactivos"*.
- Badge `(N)` retirado del título (la cuenta vive en el subtítulo).
- Columna **ID** añadida al listado (ordenable).
- Campo **ID** (readonly) en el formulario de crear/editar/ver.

---

## 3. Snapshots de Albaranes — solo estructura + Observers (Fase 2)

### Migraciones (5 nuevas)

| Migración | Tabla | Columnas |
|---|---|---|
| `2026_05_21_110000_add_snapshots_to_albaran_lineas_personal` | `albaran_lineas_personal` | 6 (trabajador: nombre, apellidos, nº empleado, 3 tasas) |
| `2026_05_21_110001_add_snapshots_to_albaran_lineas_material` | `albaran_lineas_material` | 4 (material: descripción, unidad, nº pedido, familia) |
| `2026_05_21_110002_add_snapshots_to_albaranes_cabecera` | `albaranes` | 14 (cliente, proyecto, concepto, creador, responsable) |
| `2026_05_21_120000_add_precios_to_materiales` | `materiales` | 2 (`precio_coste`, `precio_venta`) |
| `2026_05_21_120001_add_precios_snapshot_to_albaran_lineas_material` | `albaran_lineas_material` | 2 (snapshot precios) |

**Total: 28 columnas nuevas**. Todas `NULL`able; los albaranes existentes
no se rompen.

### Permiso nuevo (Material)
- **`materiales.gestionar_precios`** (web, categoría materiales).
- Excluido de `administrador` → por defecto solo superadmin.
- Controla ver/editar **`precio_coste`** (interno, para informes) y
  **`precio_venta`** (visible al cliente en albarán).

### Material — UI
- Migración añade los 2 campos al modelo + casts decimal:2.
- `MaterialForm`: 2 propiedades, validaciones, parseo decimal en `save()`.
- Material **Editar/Ver**: nueva subsección **"Precios (€)"** con
  `Precio coste €` / `Precio venta €`. Visible solo con permiso.

### Los 3 Observers (la pieza clave)

Todos usan el evento **`saving`** con la regla universal:
> **El snapshot se (re)escribe SOLO si su FK cambia (`isDirty('xxx_id')`).**
> Si editas observaciones, horas, fecha… los snapshots NO se tocan.
> Esto permite al admin **editar los snapshots a mano** sin sobreescrituras.

| Observer | Modelo | Snapshots que rellena |
|---|---|---|
| `AlbaranLineaPersonalObserver` (nuevo) | `AlbaranLineaPersonal` | nombre, apellidos, nº empleado, tasa_hora, tasa_extra, tasa_festivo |
| `AlbaranLineaMaterialObserver` (extendido) | `AlbaranLineaMaterial` | descripción, unidad, nº pedido, familia, precio_coste, precio_venta. **Mantiene** el ajuste de stock previo en `created`/`updated`/`deleted` |
| `AlbaranObserver` (nuevo) | `Albaran` (cabecera) | cliente (código/nombre/CIF), proyecto (código/nombre), concepto (nombre), creador (username/nombre/apellidos/nº empleado), responsable (username/nombre/apellidos/nº empleado). Maneja correctamente los FKs nullables |

Todos registrados en `AppServiceProvider::boot()`.

### Verificación post-`migrate:fresh --seed`

```
Albaran #1 (cliente_id=2)
  cliente_nombre_snapshot:        Empresa Quintanilla-Maya  ✓
  proyecto_nombre_snapshot:       Et aperiam fuga           ✓
  creador_username_snapshot:      ezepeda                   ✓
  responsable_username_snapshot:  oriera                    ✓

Lineas personal:
  L#1 → Esther Caraballo (tasa_h=NULL — la factory no asigna tasas)

Lineas material:
  L#1 → Panel LED 60x60 / ud / PED-1974 / Cables H07V-K  ✓
```

Las tasas y precios salen NULL en los demos porque las factories no los generan
(decisión limpia: datos sensibles fuera de demos).

---

## 4. Decisiones tomadas y por qué

| Decisión | Por qué |
|---|---|
| **`id` como código visible de Usuario** (no creamos `codigo_usuario`) | Los usuarios son entidades internas; el `id` autoincremental ya cumple |
| **3 tasas (base/extra/festivo) DECIMAL(8,3)** | Cubre los valores reales del Excel del cliente; sobra precisión |
| **Permiso `usuarios.gestionar_tarifas` excluido del admin** | Tarifa = dato sensible; gestionable desde Roles sin tocar código |
| **Tasas en línea personal del albarán** (no en cabecera) | Pueden coexistir trabajadores con distintas tasas en el mismo albarán |
| **Snapshots en columnas tipadas** (no JSON único) | Permite queries/agregados (informes de coste por concepto, etc.) |
| **Regla única `isDirty(FK)` para todos los snapshots** | Predecible, sin sorpresas, editable a mano por admin. Si quieren refrescar → borrar + recrear |
| **FKs (cliente_id, material_id, …) se mantienen intactas** | Snapshots son ADICIONALES, no sustitutos. Los FKs permiten informes y navegación |
| **Material `precio_coste` + `precio_venta`** | `coste` interno (margen) + `venta` (visible al cliente). DECIMAL(10,2) |
| **NO descuentos** en el albarán | El albarán no es factura. Los descuentos pertenecen al programa de facturación (Sage/Holded/Factusol) |
| **NO snapshot de stock** | Stock es volátil global; congelar "había 50 unidades" no aporta nada — lo importante es la cantidad consumida (ya en la línea) |
| **Stock se sigue ajustando automáticamente** | `AlbaranLineaMaterialObserver` mantiene la lógica de stock previa además de añadir snapshots |
| **`snapshot_data` JSON pre-existente en `albaranes`** | NO se toca — puede usarse para otro fin de la app. Mis snapshots viven en columnas tipadas aparte |
| **`albaran_firmas` (firmas)** | Tabla aún no creada en BD. Cuando exista, se le añade snapshot del firmante (tema aparte) |
| **Bloqueo al eliminar usuario con dependencias se mantiene** | Aunque tengamos snapshots, mantenemos el bloqueo estricto (decisión expresa del usuario) |

---

## 5. Estado de calidad

| Verificación | Resultado |
|---|---|
| `php -l` en los archivos PHP nuevos/modificados | ✅ |
| **Pint** | ✅ passed |
| `migrate:fresh --seed` | ✅ corre limpio |
| Permission cache reseteada | ✅ |
| Permisos en BD: `usuarios.gestionar_tarifas`, `materiales.gestionar_precios` | ✅ existen; admin NO los tiene; superadmin SÍ |
| 28 columnas snapshot existen | ✅ |
| Observers funcionan: snapshots rellenos en albaranes demo recién generados | ✅ verificado en BD |

**No verificado** (te toca probarlo en navegador):
- Sección "Tasas (€/hora)" en el modal/página de Usuario, gated por permiso.
- Sección "Precios (€)" en la página de Material, gated por permiso.
- Importar Usuarios con columnas de tasas.
- Exportar Usuarios con tasas en Excel/PDF (solo si tienes el permiso).

---

## 6. **Pendiente antes de seguir con Albaranes** — repaso global

Antes de meternos con la UI de albaranes (formularios, lectura de snapshots, edición manual de tasas/precios por línea, etc.), conviene **revisar/testear** los módulos base que ya tocamos para asegurarnos de que todo funciona como debe.

### 6.1 Conceptos
- [ ] Crear, editar, eliminar concepto desde la pantalla.
- [ ] Listado: filtros, búsqueda, papelera (si aplica).
- [ ] Permisos: que admin pueda lo que toca; trabajador/responsable no.
- [ ] Bloque de "vinculaciones" al editar (proyectos y albaranes que lo usan): verificar que carga y muestra correctamente.
- [ ] Comprobar incoherencia pendiente: ¿botones import/export en pantalla sin permisos `conceptos.*` en seeder? Decidir si se implementa o se quita.

### 6.2 Clientes
- [ ] Crear, editar, ver, eliminar (con bloqueo por dependencias).
- [ ] Listado: filtros (estado, provincia), búsqueda, contador `(N)` en subtítulo.
- [ ] Papelera (checkbox superadmin) — restauración solo superadmin.
- [ ] Importar Excel/CSV con mapeo de columnas + reglas all-or-nothing.
- [ ] Exportar Excel, PDF Vertical, PDF Horizontal con filtros activos.
- [ ] Modal informativo cuando intentas eliminar con dependencias.
- [ ] Verificar las pestañas de Albaranes/Proyectos/Usuarios en Ver/Editar.

### 6.3 Usuarios
- [ ] Crear, editar, ver, eliminar (con bloqueo por proyectos/albaranes).
- [ ] Listado: filtros (estado, tipo, rol, empresa), búsqueda.
- [ ] Papelera y restauración con permiso `usuarios.gestionar_papelera`.
- [ ] Jerarquía: admin no ve superadmins; no puede crear roles superiores al suyo.
- [ ] Modal de duplicados no-bloqueante al guardar (email/DNI/CIF).
- [ ] Sugerencia automática de username.
- [ ] **Tasas (€/h)** — sección visible solo con `usuarios.gestionar_tarifas`.
- [ ] **Importar/Exportar** Usuarios — campos de tasas opcionales.
- [ ] Column ID en listado, campo ID readonly en formulario.
- [ ] Pestañas Albaranes/Proyectos en Ver/Editar.

### 6.4 Proyectos
- [ ] Crear, editar, ver, eliminar.
- [ ] Asignación de tipo, cliente, responsable principal.
- [ ] Pivots: trabajadores asignados, materiales asignados, conceptos asignados.
- [ ] Listado: filtros, búsqueda, papelera (si aplica).
- [ ] Permisos.
- [ ] Decisión pendiente: ¿bloqueo por dependencias al eliminar (albaranes)?
      Mismo patrón que clientes/usuarios.

### 6.5 Materiales
- [ ] Crear, editar, ver, eliminar.
- [ ] Páginas dedicadas (Ver / Editar) con tabs Material / Albaranes / Proyectos.
- [ ] Listado: filtros (familia, pedido, stock), búsqueda, papelera.
- [ ] Permisos.
- [ ] **Precios (€)** — sección visible solo con `materiales.gestionar_precios`.
- [ ] Stock se descuenta correctamente al crear líneas de albarán (Observer existente).
- [ ] Decisión pendiente: ¿import/export de Materiales? No existe aún.

### 6.6 Familias de Material
- [ ] Crear, editar, eliminar familia.
- [ ] Asignación masiva de materiales a una familia (UX del modal que estaba pendiente de rework).
- [ ] Soft delete: si eliminas familia, los materiales quedan con `familia_id = NULL`.
- [ ] Permisos.

### 6.7 Nº Pedido (NumeroPedido)
- [ ] Crear, editar, eliminar pedido.
- [ ] `restrictOnDelete` impide borrar pedido con materiales asociados.
- [ ] Listado, búsqueda.
- [ ] Permisos.

---

## 7. Siguiente Fase 2.5 (Albaranes — UI)

Cuando hayas terminado el repaso anterior, atacaremos los formularios de albarán:

- Mostrar/editar los **snapshots de tasas** en cada línea de personal (inputs decimales gated por `usuarios.gestionar_tarifas`).
- Mostrar/editar el **snapshot de precio_venta** en cada línea de material (input decimal gated por `materiales.gestionar_precios`).
- Mostrar los snapshots de cabecera (cliente, proyecto, concepto, creador, responsable) en la vista del albarán.
- Revisar el PDF del albarán para que use los snapshots, no JOINs dinámicos.
- Decidir UX de **"refrescar snapshot"**: el patrón es borrar+recrear la línea, pero quizá ofrecer un botón explícito si es muy común.
- Confirmar bloqueo de edición en estados firmado/facturado/anulado.

---

## 8. Memoria persistente

- `albaran-snapshots-observers` — patrón completo de los 3 Observers + regla `isDirty`.
- `usuarios-import-export-papelera` — incluye ahora las tasas integradas.
- `clientes-eliminar-papelera` — bloqueo por dependencias con `Response::deny()`.

---

## 9. Riesgos / notas

- **Sin red de tests automáticos**: la suite original sigue rota por la
  migración previa de `codigo_cliente` con `SHOW INDEX` bajo SQLite (incidencia
  documentada en sesiones anteriores). Hay que probar todo en navegador.
- **Demos** generados ahora tienen snapshots en cabecera/material/personal,
  pero las tasas/precios son NULL porque las factories no los generan.
  Si quieres demos más realistas, podemos ajustar `UserFactory` y
  `MaterialFactory` para generar valores aleatorios. Es trivial.
- **Las firmas** (`albaran_firmas`) aún no existen como tabla. Cuando se cree,
  habrá que añadirle snapshots del firmante siguiendo el mismo patrón.
- **El usuario `admin` (demo)** no tiene los permisos `gestionar_tarifas` ni
  `gestionar_precios` por defecto — para probarlos hay que entrar como
  `superadmin` o asignarle el permiso desde Roles.
