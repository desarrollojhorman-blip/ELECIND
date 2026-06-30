# Avance — Wizard "Convertir borrador a albarán" y rol Jefe de equipo (01/06/2026)

Continuación del documento de diseño previo [290526_1500_idea_jefeequipo.md](290526_1500_idea_jefeequipo.md). Hoy se ha **implementado** el wizard de conversión de borrador a albarán y se ha resuelto la "paradoja del cliente" con un observer. Al final de este documento queda una sección **Pendiente — controlar lo de borradores y jefe de equipo** con lo que aún no está cerrado.

---

## 1. Objetivo del día

Tener funcionando el flujo principal del Jefe de equipo: **abrir un borrador del trabajador, normalizar lo que venga en texto libre (cliente, proyecto, concepto, responsable) y convertirlo a un albarán oficial** sin contradecir el scope (solo ve sus clientes asignados).

El wizard es la pieza donde más decisiones se concentran porque mezcla: lectura del borrador, creación de entidades dependientes (cliente → proyecto → concepto → responsable), filtrado por scope y autorización por permiso. Por eso ha sido el grueso del trabajo.

---

## 2. Resultado final: wizard de 5 pasos

Tras un par de iteraciones (ver §4) el wizard ha quedado en **5 pasos**:

1. **Cliente** — elegir uno existente del listado scoped o crear uno nuevo (si tiene `clientes.crear`).
2. **Proyecto** — depende del paso anterior:
   - Cliente **existente** → 2 opciones: *elegir un proyecto del cliente* o *crear nuevo*.
   - Cliente **nuevo** → 1 sola opción: *crear nuevo proyecto* (no se puede asignar un proyecto existente porque `proyectos.cliente_id` es FK 1·N: un proyecto solo puede pertenecer a un cliente, y los existentes ya tienen dueño).
3. **Concepto** — 2 ó 3 grupos:
   - Si el proyecto existe → *conceptos del proyecto* + *otros conceptos* + *crear*.
   - Si el proyecto es nuevo → *todos los conceptos* + *crear*.
4. **Responsable** — opcional. 3 modos: *sin responsable*, *elegir del cliente*, *crear nuevo*. El listado "elegir" se subdivide en 2 grupos solo cuando el proyecto existe: *responsables ya en este proyecto* + *otros responsables del cliente*. El responsable se filtra por `cliente_id` (es usuario externo).
5. **Confirmar** — resumen visual de las 4 decisiones + nº de líneas (personal/material) que se copiarán. Hasta este momento **nada está en BD**.

Toda la creación se hace en una sola `DB::transaction` en `confirmar()`: cliente → proyecto → concepto + sync al pivote del proyecto → responsable + sync al pivote del proyecto con `rol_en_proyecto='responsable'` → albarán → copia de líneas con FK real → marca el borrador como `convertido`.

### 2.1 Materiales y trabajadores NO se gestionan en el wizard

Decisión explícita tras una iteración intermedia (ver §4): **no se eligen materiales ni trabajadores en el wizard**. Razones:
- Es muy denso para el jefe (listados largos, módulo de materiales puede estar OFF, riesgo de tocar stock/RRHH).
- El borrador ya trae líneas con FK real (las que el trabajador eligió bien desde el móvil): esas se copian tal cual al albarán.
- Las líneas que el trabajador escribió en texto libre (`material_texto`, `trabajador_texto`) se vuelcan como aviso al campo `observaciones` del albarán, con el formato `⚠ Material en texto libre: «...» — sin asignar.` (idem para trabajadores). El **administrador** las gestiona luego desde la página de albaranes.

---

## 3. La "paradoja del cliente" resuelta con `ClienteObserver`

El documento previo identificaba la paradoja: si el jefe es scoped (solo ve sus clientes asignados) y crea uno nuevo, ese cliente *no estaría asignado a él* → no lo vería. El documento previo proponía *no darle el permiso*. Hoy hemos optado por la **Opción A**: dárselo y **auto-asignar** el cliente recién creado al pivot del creador.

- **[app/Observers/ClienteObserver.php](app/Observers/ClienteObserver.php)** — método `created()`: si el usuario autenticado está scoped (`idsClientesGestionados() !== null`), añade el cliente recién creado a `cliente_user_gestion` con `syncWithoutDetaching`. Si no está scoped (admin/superadmin/jefe con "todos los clientes"), no hace nada — ya verá el cliente igualmente.
- **Registro** en [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) con `Cliente::observe(ClienteObserver::class)`.

Misma idea, extendida hoy: el jefe puede crear **proyecto** (el proyecto hereda `cliente_id` que ya está bajo su scope) y crear **responsable** (queda con `cliente_id` del mismo cliente, también bajo scope). No hace falta observer para esos casos: la herencia del `cliente_id` ya los sitúa dentro del scope.

---

## 4. Iteraciones — por qué el wizard pasó por 5 → 7 → 5

1. **5 pasos iniciales** (Cliente / Proyecto / Concepto / Responsable / Confirmar). Versión mínima funcional.
2. **Expansión a 7 pasos**: se añadieron *Materiales* y *Trabajadores* con multi-select agrupado (los del proyecto / otros), pensando que el jefe quisiera curar la lista del albarán y asociar al proyecto a la vez. Backend y blade implementados.
3. **Vuelta a 5 pasos** (decisión del usuario): "muy complejo gestionar esto en el borrador". Razonamiento: el borrador es para que el trabajador *plantee*, no para que el jefe *cure* RRHH/almacén. La curación la hace el admin desde Albaranes con todos los datos delante.

Lección rescatable: cuando el wizard empieza a crecer en pasos opcionales, conviene preguntarse si el paso resuelve un problema que el usuario realmente tiene **en ese momento** o si está adelantando trabajo que pertenece a otra pantalla.

También se descartó un grupo "*Otros proyectos*" en el paso 2 (proyectos de otros clientes). Razón: con FK 1·N no se puede reasignar un proyecto a un cliente distinto sin romper el modelo.

---

## 5. Permisos del Jefe de equipo (estado actual)

En [database/seeders/RolesAndPermissionsSeeder.php](database/seeders/RolesAndPermissionsSeeder.php), el bloque `$jefeEquipo->syncPermissions([...])` ha quedado con:

- Borradores: `ver_todos`, `modificar`, `convertir`
- Albaranes: `ver_todos`, `crear_web`, `modificar`, `imprimir`, `exportar`, `descargar_pdf`, `solicitar_firma`, `facturar`
- Clientes: `ver`, **`crear`** (con auto-asignación vía observer)
- Proyectos: `ver`, **`crear`** (heredan cliente bajo scope)
- Conceptos: `ver`, `crear`
- Materiales: solo `ver` (se quitó `crear`: con el wizard simplificado ya no se usa)
- Usuarios: **`crear_responsable`** (para crear responsable durante el wizard)

El rol está marcado con `solo_clientes_asignados = true` (flag en `roles`) — esa es la pieza que activa el scoping en código (a través de `User::idsClientesGestionados()` y los scopes de las queries de cada Eloquent involucrado).

---

## 6. Cambios por archivo

### Backend
- **[app/Livewire/Borradores/Convertir.php](app/Livewire/Borradores/Convertir.php)** — componente del wizard. 5 pasos, computeds para cada listado scoped, validación por paso (`validarCliente/validarProyecto/...`), `confirmar()` transaccional, `componerObservaciones()` que mezcla observaciones del borrador + avisos por líneas en texto libre.
- **[app/Observers/ClienteObserver.php](app/Observers/ClienteObserver.php)** — auto-asigna a creador scoped.
- **[app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php)** — registra el observer.

### Frontend
- **[resources/views/livewire/borradores/convertir.blade.php](resources/views/livewire/borradores/convertir.blade.php)** — vista del wizard. Stepper estático de 5 pasos, paso 2 con dos ramas según `clienteModo`, paso 3 y 4 con grupos condicionados a la existencia del proyecto, paso 5 con resumen + aviso de que nada está guardado todavía. Patrón `<x-ui.searchable-select>` con `:value` siempre presente (memoria [[searchable-select-write-only]]).

### Seeder
- **[database/seeders/RolesAndPermissionsSeeder.php](database/seeders/RolesAndPermissionsSeeder.php)** — permisos del Jefe ajustados (§5).

---

## 7. Lo que ya estaba hecho antes de hoy (recordatorio rápido)

Para no quedar descontextualizado en futuras sesiones, lo importante que **ya estaba** y que el wizard asume:

- Modelo dos-flags en `roles`: `solo_clientes_asignados` (data scoping) + `es_externo` (deriva `tipo_usuario`).
- Pivot `cliente_user_gestion` para "qué clientes gestiona cada jefe".
- Pivot `proyecto_usuario` con columna `rol_en_proyecto` (`trabajador` | `responsable`).
- Pivot `proyecto_concepto` (N:M conceptos↔proyecto).
- FK directa `proyectos.cliente_id` (1 cliente por proyecto).
- `User::idsClientesGestionados()` devuelve `null` cuando el usuario no es scoped, o array de IDs si lo es.
- `BorradorPolicy::convertir()` autoriza la entrada al wizard.
- Política y scope de borradores con `orWhereNull('cliente_id')` para que el jefe vea también los borradores sin asignar (los típicos partes que llegan con texto libre).

---

## 8. Pendiente — "controlar lo de borradores y jefe de equipo"

Lo que aún no está cerrado para considerar este flujo terminado. Esta es la **lista a abordar en la próxima sesión**:

### 8.1 Verificación end-to-end real
- [ ] Crear un borrador desde móvil con campos en texto libre (cliente, proyecto, concepto, responsable, líneas de material y personal).
- [ ] Iniciar sesión como Jefe de equipo, verificar que ese borrador aparece en su listado.
- [ ] Abrir el wizard y recorrer los 5 pasos eligiendo en cada paso ambas ramas en pruebas separadas (existente / nuevo).
- [ ] Confirmar y verificar:
  - Albarán creado con número correcto.
  - Líneas con FK copiadas, líneas en texto libre concatenadas en `observaciones`.
  - Pivotes del proyecto sincronizados (concepto y responsable).
  - Borrador marcado como `convertido` con `convertido_a_albaran_id`.
  - Cliente recién creado (si toca) aparece después en el listado del jefe (gracias al observer).

### 8.2 Controlar el listado de borradores para el jefe
Revisar específicamente que `app/Livewire/Borradores/Index.php` (o equivalente) hace bien:
- [ ] Aplica el scope: borradores con `cliente_id` IN sus clientes gestionados + borradores con `cliente_id IS NULL` (sin asignar).
- [ ] Pills/filtros visibles solo para usuarios scoped: *Todos* / *Asignados* / *Por revisar* (los `null`).
- [ ] Botón "Convertir a albarán" visible solo si la policy lo autoriza.
- [ ] Comportamiento al cambiar de página, ordenar, buscar.

### 8.3 Controlar el listado de albaranes para el jefe
- [ ] El listado de albaranes filtra por sus clientes gestionados.
- [ ] Permisos por acción: `crear_web`, `modificar`, `imprimir`, `exportar`, `solicitar_firma`, `facturar` se reflejan en los botones de la UI (no solo en la policy).
- [ ] Verificar que el albarán recién creado vía wizard aparece en su listado.

### 8.4 Control de borradores "huérfanos" (sin cliente_id)
- [ ] Confirmar el comportamiento cuando varios jefes ven el mismo borrador con `cliente_id IS NULL`. ¿Lo "toma" el primero que lo abre? ¿Lo bloquea? ¿O cualquiera puede convertirlo? Decisión pendiente.

### 8.5 Repaso de la pantalla "Ver borrador" (`borradores.ver`)
- [ ] Que el jefe pueda **ver** un borrador sin convertirlo y entender qué se va a perder/transformar.
- [ ] Botón "Convertir" desde aquí lleva al wizard.
- [ ] Aviso visual si hay líneas en texto libre (potencial pérdida si no las cura el admin después).

### 8.6 Tests
- [ ] Test de feature: jefe convierte borrador eligiendo cliente nuevo + proyecto nuevo + concepto nuevo + responsable nuevo → todo se crea, observer auto-asigna cliente, pivotes correctos, albarán generado.
- [ ] Test que verifica que un jefe **NO puede** convertir un borrador de un cliente que no gestiona.
- [ ] Test que cubre líneas en texto libre → observaciones.
- Nota sobre suite global: la suite está rota por pre-existente (memoria [[suite-tests-rota-show-index]]) — escribir tests aislados que no dependan de migrar todo el esquema bajo SQLite, o usar MySQL en test.

### 8.7 Detalles secundarios
- [ ] Revisar si en el resumen del paso 5 hay textos que mencionan "materiales/trabajadores" antiguos (limpieza final).
- [ ] Comprobar que `searchable-select` mantiene el valor seleccionado al volver al wizard tras un error de validación (race condition documentada en memoria).
- [ ] El campo `cliente_texto` / `proyecto_texto` / `concepto_texto` / `responsable_texto` del borrador, ¿se borran tras convertirlo o quedan como histórico? Decisión documentada en otro sitio o pendiente.

---

## 9. Cómo usar este documento en futuras sesiones

Leer en orden: §1 → §2 (estado final) → §3 (paradoja) → §5 (permisos) → §8 (lo que falta). El §4 (iteraciones) y §7 (recordatorio previo) son contexto que probablemente ya esté en memoria si el agente entra fresco.

Cuando vuelva a abrirse este flujo, comprobar antes de tocar nada:
- `git log --oneline` para ver qué se ha hecho desde 01/06/2026.
- Que `BorradorPolicy::convertir` y los scopes siguen como se asume aquí.
- Que el observer sigue registrado (a veces se pierden en refactors de AppServiceProvider).
