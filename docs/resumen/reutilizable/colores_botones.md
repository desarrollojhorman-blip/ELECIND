# Colores de botones

Componente: `<x-ui.button variant="...">`.

| Variante    | Color aprox.        | Fondo normal     | Hover            | Cuándo usarlo                              |
|-------------|---------------------|------------------|------------------|--------------------------------------------|
| `success`   | Verde esmeralda     | `emerald-600`    | `emerald-700`    | **Nuevo** — crear un registro              |
| `info`      | Azul                | `blue-600`       | `blue-700`       | **Guardar** — persistir cambios            |
| `danger`    | Rojo                | `red-600`        | `red-700`        | **Eliminar** — acción destructiva          |
| `neutral`   | Gris medio          | `slate-600`      | `slate-700`      | **Todos / Deshacer / Editar / Cancelar**   |
| `warning`   | Ámbar / naranja     | `amber-500`      | `amber-600`      | Acciones de advertencia (ej. resetear)     |
| `primary`   | Rojo oscuro (marca) | `primary-600`    | `primary-700`    | Acción principal del color de empresa      |
| `secondary` | Gris carbón         | `#1f2937`        | `#111827`        | Acción secundaria oscura                   |
| `ghost`     | Sin color           | transparente     | `slate-100`      | (evitar — usar `neutral` o `outline`)      |
| `outline`   | Blanco con borde    | `white`          | `slate-50`       | Alternativa a ghost con borde visible      |
| `link`      | Solo texto          | transparente     | —                | Texto clicable inline, sin caja            |

---

## Botones estándar por página

### Páginas de edición / creación

**Grupo izquierda** (`actionsLeft`):
- `neutral` + `heroicon-o-list-bullet` → **Todos** (siempre)
- `success` + `heroicon-o-plus` → **Nuevo** (`@can create`, solo en modo edición)
- `danger` + `heroicon-o-trash` → **Eliminar** (`@can delete`, solo en modo edición)

**Grupo derecha** (`actionsRight`):
- `neutral` + `heroicon-o-arrow-uturn-left` → **Deshacer**
- `info` + `heroicon-o-check` → **Guardar**

### Páginas de solo lectura (ver)

**Grupo izquierda** (`actionsLeft`) — orden fijo:
1. `neutral` + `heroicon-o-list-bullet` → **Todos** (siempre)
2. `neutral` + `heroicon-o-pencil-square` → **Editar** (`@can update`)
3. `success` + `heroicon-o-plus` → **Nuevo** (`@can create`)
4. `danger` + `heroicon-o-trash` → **Eliminar** (`@can delete`)

### Modales (footers)

- **Modal edición** (crear/editar): `neutral` → **Cancelar** + `success`/`info` → **Guardar**
- **Modal solo lectura** (ver): sin botones en el footer (el modal se cierra con la X)
- **Modal confirmación eliminar**: `neutral` → **Cancelar** + `danger` → **Eliminar**

---

## Icon-buttons en tabla

| Icono           | Variante  | Acción               |
|-----------------|-----------|----------------------|
| `eye`           | `neutral` | Ver detalle          |
| `pencil-square` | `info`    | Editar               |
| `trash`         | `danger`  | Confirmar eliminación|
| `arrow-uturn-left` | `success` | Restaurar de papelera |
