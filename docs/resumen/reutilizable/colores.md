# Paletas de colores y variantes de UI

> Los colores se definen como CSS variables en `resources/css/app.css` y se mapean
> al sistema de Tailwind vía `@theme`. El color primario es configurable por cliente
> desde *Configuración → Empresa* — el sistema inyecta un `<style>` en el `<head>`
> que sobreescribe las variables `:root`.

---

## Color primario (configurable)

Rojo granate por defecto (ELECIND). Escala completa disponible como `primary-{n}`.

| Token Tailwind    | CSS variable         | Hex defecto | Color aprox.          | Uso principal                        |
|-------------------|----------------------|-------------|-----------------------|--------------------------------------|
| `primary-50`      | `--c-primary-50`     | `#fdf4f4`   | Blanco rosado         | Fondo hover suave, sidebar activo    |
| `primary-100`     | `--c-primary-100`    | `#f9e2e2`   | Rosa muy pálido       | Fondo ítem activo                    |
| `primary-200`     | `--c-primary-200`    | `#f1bcbc`   | Rosa claro            | Bordes suaves                        |
| `primary-300`     | `--c-primary-300`    | `#e58e8e`   | Rosa salmón           | —                                    |
| `primary-400`     | `--c-primary-400`    | `#d35a5a`   | Rojo rosado           | —                                    |
| `primary-500`     | `--c-primary-500`    | `#b83333`   | Rojo medio            | Ring de foco, badges                 |
| `primary-600`     | `--c-primary-600`    | `#a52a2a`   | Rojo oscuro           | **Botón primary** (fondo)            |
| `primary-700`     | `--c-primary-700`    | `#871f1f`   | Granate               | Botón primary hover · cabeceras tabla|
| `primary-800`     | `--c-primary-800`    | `#6c1818`   | Granate oscuro        | Botón primary active                 |
| `primary-900`     | `--c-primary-900`    | `#561414`   | Burdeos               | —                                    |
| `primary-950`     | `--c-primary-950`    | `#2e0808`   | Casi negro rojizo     | —                                    |

---

## Acento (fijo, no configurable)

Rosa pálido. Usado en sidebar ítem activo y cabecera de modales.

| Token Tailwind | Hex       | Color aprox.       | Uso                              |
|----------------|-----------|--------------------|----------------------------------|
| `accent-50`    | `#fbf2f2` | Blanco rosado      | —                                |
| `accent-100`   | `#f5e6e6` | Rosa muy pálido    | Sidebar ítem activo (fondo)      |
| `accent-200`   | `#ecd0d0` | Rosa pálido        | Borde sutil en paneles de acento |

---

## Botones — variantes de `<x-ui.button>`

Uso: `<x-ui.button variant="success">Guardar</x-ui.button>`

| Variante    | Color fondo      | Color aprox.       | Hover            | Cuándo usarlo                        |
|-------------|------------------|--------------------|------------------|--------------------------------------|
| `primary`   | `primary-600`    | Rojo oscuro        | `primary-700`    | Acción principal de la marca         |
| `success`   | `emerald-600`    | Verde esmeralda    | `emerald-700`    | **Guardar**, confirmar, crear        |
| `danger`    | `red-600`        | Rojo               | `red-700`        | **Eliminar**, acción destructiva     |
| `info`      | `blue-600`       | Azul               | `blue-700`       | **Editar**, ver detalle, navegar     |
| `warning`   | `amber-500`      | Ámbar / naranja    | `amber-600`      | Acciones de advertencia              |
| `secondary` | `dark` (#1f2937) | Gris muy oscuro    | `dark-hover`     | Acción secundaria oscura             |
| `ghost`     | transparente     | Sin color          | `slate-100`      | **Volver**, cancelar, botones neutros|
| `outline`   | `white`          | Blanco con borde   | `slate-50`       | Alternativa a ghost con borde        |
| `link`      | transparente     | Solo texto         | —                | Texto clicable inline                |

### Tamaños disponibles

`size="xs"` · `size="sm"` · `size="md"` *(default)* · `size="lg"`

---

## Badges — tonos de `<x-ui.badge>`

Uso: `<x-ui.badge tone="success" dot>Activo</x-ui.badge>`

| Tono      | Color aprox.            | Fondo              | Texto              | Punto (`dot`)  | Cuándo usarlo               |
|-----------|-------------------------|--------------------|--------------------|----------------|-----------------------------|
| `neutral` | Gris azulado pálido     | `slate-100`        | `slate-700`        | `slate-400`    | Estado genérico, sin acción |
| `success` | Verde menta pálido      | `emerald-100`      | `emerald-700`      | `emerald-500`  | Activo, completado, OK      |
| `warning` | Ámbar muy pálido        | `amber-100`        | `amber-800`        | `amber-500`    | Pendiente, atención         |
| `danger`  | Rojo pálido             | `red-100`          | `red-700`          | `red-500`      | Error, rechazado, eliminado |
| `info`    | Azul pálido             | `blue-100`         | `blue-700`         | `blue-500`     | Informativo, en proceso     |
| `primary` | Rosado pálido (marca)   | `primary-100`      | `primary-700`      | `primary-500`  | Destacado con color de marca|
| `pending` | Amarillo suave          | `yellow-200`       | `yellow-900`       | `yellow-500`   | Borrador, sin firmar        |

---

## Semánticos de estado (CSS variables)

Usados directamente en estilos concretos (alertas, fondos de sección).

| Variable              | Hex       | Color aprox.           | Rol                        |
|-----------------------|-----------|------------------------|----------------------------|
| `--c-success-bg`      | `#d1fae5` | Verde menta muy pálido | Fondo alerta éxito         |
| `--c-success-fg`      | `#065f46` | Verde oscuro           | Texto alerta éxito         |
| `--c-warning-bg`      | `#fef3c7` | Amarillo muy pálido    | Fondo alerta aviso         |
| `--c-warning-fg`      | `#92400e` | Marrón ámbar           | Texto alerta aviso         |
| `--c-danger-bg`       | `#fee2e2` | Rojo muy pálido        | Fondo alerta error         |
| `--c-danger-fg`       | `#991b1b` | Rojo oscuro            | Texto alerta error         |
| `--c-info-bg`         | `#dbeafe` | Azul muy pálido        | Fondo alerta info          |
| `--c-info-fg`         | `#1e40af` | Azul oscuro            | Texto alerta info          |
| `--c-neutral-bg`      | `#f1f5f9` | Gris azulado pálido    | Fondo neutro               |
| `--c-neutral-fg`      | `#475569` | Gris azulado medio     | Texto neutro               |

---

## Neutros de superficie y texto

| Variable              | Hex        | Color aprox.             | Uso                                  |
|-----------------------|------------|--------------------------|--------------------------------------|
| `--c-surface`         | `#ffffff`  | Blanco                   | Tarjetas, modales, inputs            |
| `--c-surface-muted`   | `#f8fafc`  | Blanco grisáceo          | Fondo de página                      |
| `--c-surface-subtle`  | `#f1f5f9`  | Gris muy claro           | Filas alternas, secciones internas   |
| `--c-border`          | `#e2e8f0`  | Gris azulado claro       | Bordes estándar                      |
| `--c-border-strong`   | `#cbd5e1`  | Gris azulado medio       | Bordes con más contraste             |
| `--c-text`            | `#0f172a`  | Casi negro azulado       | Texto principal                      |
| `--c-text-muted`      | `#475569`  | Gris azulado oscuro      | Texto secundario                     |
| `--c-text-subtle`     | `#64748b`  | Gris azulado medio       | Texto terciario, hints               |
| `--c-text-inverse`    | `#ffffff`  | Blanco                   | Texto sobre fondos oscuros           |
| `--c-dark`            | `#1f2937`  | Gris carbón              | Botón `secondary`                    |
| `--c-dark-hover`      | `#111827`  | Gris casi negro          | Hover botón `secondary`              |
| `--c-table-header-text`| `#ffffff` | Blanco                   | Texto encabezados de tabla (config.) |

---

## Referencia rápida: acciones comunes

| Acción              | Componente                          |
|---------------------|-------------------------------------|
| Guardar / Confirmar | `<x-ui.button variant="success">`   |
| Eliminar            | `<x-ui.button variant="danger">`    |
| Editar / Ver        | `<x-ui.button variant="info">`      |
| Volver / Cancelar   | `<x-ui.button variant="ghost">`     |
| Acción de marca     | `<x-ui.button variant="primary">`   |
| Estado activo       | `<x-ui.badge tone="success" dot>`   |
| Estado inactivo     | `<x-ui.badge tone="neutral" dot>`   |
| Borrador / Pendiente| `<x-ui.badge tone="pending" dot>`   |
