# V2 — Continuación Tarifas · 19/06/2026

Cambios sobre el bloque **Tarifas** posteriores al checkpoint
[`170626_cambios.md`](./170626_cambios.md). Foco: dejar las tarifas (cliente y
trabajador) usables y consistentes según las decisiones del cliente.

---

## 1 · Un único plus: Plus Retén (cliente y trabajador)

**Decisión del cliente**: de los 3 pluses, solo se usa **Plus Retén**. Tanto el
lado cliente como el trabajador manejan ese único plus.

### Lado trabajador — Plus Retén pasa a ser editable por trabajador
- **Migración** `2026_06_17_100000_add_tasa_plus_reten_to_users.php`: nueva
  columna `users.tasa_plus_reten` decimal(8,3) NOT NULL DEFAULT 0 (aditiva).
- `User` (fillable + cast decimal:3).
- `AtributosHoraSeeder`: el atributo `plus_reten` deja de tener `mapeo_tasa = null`
  y pasa a `mapeo_tasa = 'tasa_plus_reten'` (re-sembrado, idempotente).
- `UserTasasObserver`: añadido `tasa_plus_reten` a la lista de campos vigilados
  → sus cambios se registran en `tarifas_historial` como el resto de tasas.

### Lado cliente — solo se muestra Plus Retén
- `Tarifas → Clientes` y el **bloque embebido en la ficha de cliente** ya no
  muestran `Plus Festivo` ni `Plus Noche`. Quedan **8 horas + Plus Retén = 9
  columnas**.

### Pieza común
- Nuevo scope `AtributoHora::usados()` = los 8 tipos de hora (normales + extras) +
  Plus Retén. Lo usan las 3 pantallas (Clientes Index, Clientes Bloque,
  Trabajadores Index) para no duplicar el filtro.

### ⚠️ Decisión a tener presente (no destructiva)
- **No se borraron** `Plus Festivo` ni `Plus Noche` del catálogo
  `atributos_hora`. Solo se **ocultan** de la UI. Razón: reversible y sin riesgo
  para tarifas/históricos que pudieran referenciarlos. Si en el futuro se quiere
  limpieza total, hace falta una migración aparte que borre esos 2 atributos y
  sus filas dependientes en `tarifas_cliente` / `tarifas_historial`.

---

## 2 · Trabajadores — columna «Laboral» que agrupa las 4 horas normales

**Decisión del cliente**: las 4 horas normales (Labor, Lab Noche, Fest, Fest
Noct) del trabajador **comparten siempre el mismo precio**. Se unifican en **una
sola columna** en la interfaz.

- En `Tarifas → Trabajadores` esas 4 tasas se muestran como **una columna
  «Laboral»**. Al editarla, el mismo valor se escribe en las **4 columnas reales**
  de `users` (`tasa_hora`, `tasa_lab_noche`, `tasa_festivo`, `tasa_fest_noche`).
- **La unificación es solo de interfaz**: en BD las 4 tasas siguen separadas (por
  si en el futuro divergen). El historial registra el cambio de los 4 atributos.
- Implementado con una estructura `Trabajadores\Index::COLUMNAS`, donde una
  columna puede mapear a varios campos que se editan juntos. Añadir futuros
  grupos (p. ej. unificar también extras) = añadir una entrada a ese array.

**Columnas finales de Trabajadores** (6):
`Laboral · Ex Lab · Ex Lab Noc · Ex Fes · Ex Fes Noct · Plus Retén`.

---

## 3 · Nombres/estilo de cabeceras unificados

- **Trabajadores**: la columna agrupada se llama **`Laboral`** (el cliente ya
  conoce esa columna por ese nombre).
- **Clientes** (Index + bloque de ficha): las cabeceras de atributos pasan de
  **MAYÚSCULAS** a **tipo título**, igual que Trabajadores:
  `Labor · Lab Noche · Fest · Fest Noct · Ex Lab · Ex Lab Noc · Ex Fes ·
  Ex Fes Noct · Plus Retén`.
- **Causa técnica**: en Clientes esas columnas no son ordenables → se pintaban en
  un `<span>` que heredaba el `uppercase` del `<thead>` global de
  `x-ui.data-table`; en Trabajadores van en un `<button>` que no lo hereda. Se
  igualó añadiendo `style="text-transform: none"` solo en esas celdas (vía el
  reenvío de atributos de `x-ui.sortable-header`). No se tocó el `<thead>` global
  ni se necesitó reconstruir Tailwind (la clase `normal-case` no está en el CSS
  compilado de producción).

---

## 4 · Archivos tocados

**Nuevos**
- `database/migrations/2026_06_17_100000_add_tasa_plus_reten_to_users.php`

**Modificados**
- `app/Models/AtributoHora.php` — scope `usados()`.
- `app/Models/User.php` — `tasa_plus_reten` (fillable + cast).
- `app/Observers/UserTasasObserver.php` — vigila `tasa_plus_reten`.
- `database/seeders/AtributosHoraSeeder.php` — `plus_reten → tasa_plus_reten`.
- `app/Livewire/Tarifas/Trabajadores/Index.php` — `COLUMNAS` (grupo «Laboral»),
  `editar()`/`guardar()` por columna, `atributosHora()` usa `usados()`.
- `app/Livewire/Tarifas/Clientes/Index.php` — `atributos()` usa `usados()`,
  `editar()` precarga solo los atributos visibles.
- `app/Livewire/Tarifas/Clientes/Bloque.php` — idem.
- `resources/views/livewire/tarifas/trabajadores/index.blade.php` — tabla por
  columnas.
- `resources/views/livewire/tarifas/clientes/index.blade.php` — cabeceras tipo
  título.
- `resources/views/livewire/tarifas/clientes/bloque.blade.php` — cabeceras tipo
  título.

---

## 5 · Verificación realizada
- Migración + re-seed aplicados; `usados()` devuelve 9 atributos
  (8 horas + plus retén); `plus_reten → tasa_plus_reten` correcto.
- Prueba end-to-end del grupo «Laboral»: editar la columna escribió las 4 tasas
  normales a la vez y el extra quedó independiente; el historial registró cada
  atributo. Datos de prueba limpiados después.
- Cabecera de Clientes renderiza con `text-transform: none` (verificado el HTML
  generado por el componente).

---

## 6 · Pendiente / a controlar
- **Cambios sin commitear** (estos + el bloque Partes albarán-style del 17/06 +
  el Informe). Hacer commit cuando se valide el flujo.
- **Limpieza opcional** de `Plus Festivo` / `Plus Noche` del catálogo (ver §1) —
  solo si el cliente confirma que no se usarán nunca.
- **Coste real del Plus Retén**: ahora es editable por trabajador (columna), pero
  queda confirmar de dónde salen los importes y cómo entra en el cálculo de
  partes (sigue dependiente de la decisión de fondo §4.4 del 170626: modelo de
  cálculo de horas en el parte).
- **Informe de horas**: sigue aparcado hasta cerrar el modelo de cálculo.

Este documento no se reescribe; lo siguiente va en un `.md` nuevo de esta carpeta.
