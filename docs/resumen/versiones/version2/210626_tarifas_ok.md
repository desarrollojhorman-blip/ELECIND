# V2 — Tarifas completadas · 21/06/2026

Resumen de todo lo implementado en el módulo Tarifas durante esta sesión.
Parte anterior documentada en `190626_contunuarTarifas.md`.

---

## 1 · Ordenación en Tarifas → Clientes (global)

- Añadida ordenación (asc/desc) en **todas las columnas**: Código, Cliente,
  Tipo proyecto y los 9 atributos (Labor, Lab Noche, Fest, Fest Noct, Ex Lab,
  Ex Lab Noc, Ex Fes, Ex Fes Noct, Plus Retén).
- Las columnas de atributo ordenan vía `LEFT JOIN tarifas_cliente` por importe
  (NULL primero en asc).
- Finalmente se **quitó la ordenación de las columnas de precio** (solo queda
  en Código, Cliente, Tipo proyecto) — simplifica la cabecera y el backend.

---

## 2 · Paginación en Tarifas → Clientes (global)

- **Bug corregido**: la paginación usaba `LengthAwarePaginator` manual con
  `request()->url()`, que en Livewire AJAX devuelve el endpoint de Livewire,
  no la URL de la página → los links de paginación nunca cambiaban de página.
- **Solución**: eliminada la propiedad `$paginaActual` y el paginador manual;
  se usa el trait `WithPagination` de Livewire 3 con `->paginate()`. Todos los
  `$this->paginaActual = 1` cambiaron a `$this->resetPage()`.

---

## 3 · Bulk apply — Tarifas → Clientes (global)

Permite aplicar el mismo importe a **todas las combinaciones filtradas** de
una columna de precio con un solo clic.

- Cada cabecera de columna de precio es ahora un **botón** que abre un modal.
- El modal muestra el nombre del atributo y un input de importe.
- Al confirmar, aplica a **todas las filas que pasen los filtros activos**
  (buscar / cliente / tipo de proyecto), en todas las páginas.
- Clientes o tipos de proyecto inactivos nunca aparecen en el cross-join →
  nunca se tocan.
- Métodos: `abrirBulk(atributoId)`, `cerrarBulk()`, `aplicarBulk()`.

---

## 4 · Bulk apply — Tarifas → Clientes (bloque en ficha de cliente)

Misma funcionalidad para la pestaña "Tarifas" dentro de la ficha de un
cliente (`Livewire\Tarifas\Clientes\Bloque`).

- Cada cabecera de columna es un botón que abre el modal genérico.
- Aplica a **todos los tipos de proyecto activos** de ese cliente.
- En modo `$soloLectura = true` o sin permiso `tarifas.editar_clientes`,
  las cabeceras son texto estático.
- Métodos: `abrirBulk(atributoId)`, `cerrarBulk()`, `aplicarBulk()`.

---

## 5 · Bulk apply — Tarifas → Trabajadores (global)

Misma funcionalidad para la tabla de trabajadores
(`Livewire\Tarifas\Trabajadores\Index`).

- Columnas definidas en `COLUMNAS` (constante): "Laboral" agrupa 4 campos
  (`tasa_hora`, `tasa_lab_noche`, `tasa_festivo`, `tasa_fest_noche`).
  Al hacer bulk en "Laboral", se escriben las 4 columnas con el mismo valor.
- Aplica a **todos los trabajadores que pasen los filtros activos**
  (buscar / filtroRol), en todas las páginas.
- Actualiza modelo por modelo (no query builder) para que `UserTasasObserver`
  registre el historial de cada trabajador afectado.
- Refactorización: query con filtros extraída a `buildFilteredQuery()` para
  reutilizarla tanto en `render()` como en `aplicarBulk()`.
- Métodos: `abrirBulk(columnaKey)`, `cerrarBulk()`, `aplicarBulk()`.

---

## 6 · Consistencia entre los 4 puntos de entrada de tarifas

Los 4 accesos al dato (Clientes global, ficha cliente, Trabajadores global,
ficha usuario) ya son consistentes:

| Pantalla | Tabla modificada | Historial |
|---|---|---|
| Tarifas → Clientes | `tarifas_cliente` | `TarifaClienteObserver` |
| Ficha cliente → Tarifas | `tarifas_cliente` | `TarifaClienteObserver` |
| Tarifas → Trabajadores | `users.tasa_*` | `UserTasasObserver` |
| Ficha usuario → Tarifas | `users.tasa_*` | `UserTasasObserver` |

- En la ficha de usuario, "Laboral" muestra un único campo que escribe las 4
  tasas normales a la vez (igual que la columna "Laboral" de la tabla masiva).
- `tasa_plus_reten` añadida a la ficha de usuario (migración aditiva
  `2026_06_17_100000_add_tasa_plus_reten_to_users.php`).

---

## 7 · Archivos modificados en esta sesión

- `app/Livewire/Tarifas/Clientes/Index.php`
- `app/Livewire/Tarifas/Clientes/Bloque.php`
- `app/Livewire/Tarifas/Trabajadores/Index.php`
- `app/Livewire/Forms/UserForm.php`
- `resources/views/livewire/tarifas/clientes/index.blade.php`
- `resources/views/livewire/tarifas/clientes/bloque.blade.php`
- `resources/views/livewire/tarifas/trabajadores/index.blade.php`
- `resources/views/livewire/usuarios/editar.blade.php`
- `database/factories/UserFactory.php` (fix null → 0 en tasas externas)
- `database/seeders/DatabaseSeeder.php` (añadido Fase4DemoSeeder)
- `database/seeders/Fase3DemoSeeder.php` (fix DST en aprobado_at)

---

## 8 · Pendiente (próxima sesión)

- **Partes**: terminar de configurarlos correctamente (clon de Albaranes).
- **Informes de horas**: depende de que los partes estén bien.
- Limpieza opcional de `Plus Festivo` / `Plus Noche` del catálogo
  `atributos_hora` si el cliente confirma que no se usarán.
