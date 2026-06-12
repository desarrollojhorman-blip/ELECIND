# 🛡️ Extensión post-cierre Fase 1 — Iteración 5 · CRUD Roles + Refactor de nombres

**Fecha:** 14/05/2026
**Hora:** 01:00 (post-cierre Fase 1)
**Estado:** ✅ EXTENSIÓN COMPLETADA · 126/126 tests verde

Continuación de [`130526_2300_cierre_fase_1.md`](./130526_2300_cierre_fase_1.md). Tras dar la Fase 1 por cerrada, en la misma sesión se decidió:

1. **Anticipar el CRUD de Roles personalizados** (estaba pospuesto a Fase 4) aprovechando el contexto fresco de jerarquía por nivel.
2. **Consolidar el refactor de nombres** detectado durante el cierre (`empresas_clientes` → `clientes`, `configuracion_empresa` → `empresa`, `empresa_cliente_id` → `cliente_id`) antes de empezar Fase 2 para no arrastrar inconsistencias.

---

## 🎯 Lo que se completó en esta iteración (5)

### 🛡️ CRUD `Roles y permisos` con ámbito + delegación

Pantalla `/configuracion/roles` (bajo el submenú "Configuración") con tabla y modal de alta/edición. Es el CRUD más complejo de Fase 1 por las **3 reglas transversales** que aplica simultáneamente:

#### Modelo de permisos en tres capas

1. **Permisos de acción** (CRUD por entidad) — ya existían.
2. **Permisos de UI** (`*.exportar`, `*.importar`, `*.imprimir`) — añadidos en este sprint.
3. **Permisos de propiedad** (`*.ver_todos` vs `*.ver_propios`) para albaranes, ausencias e incidencias.

Total: **54 permisos** clasificados en 13 categorías.

#### Nuevas columnas en `permissions`

- `ambito` (enum `web|movil|ambos`) — define en qué app es válido el permiso.
- `descripcion` (string, 200 chars) — texto humano explicativo.
- `categoria` (string, 60 chars) — agrupador para la UI (albaranes, clientes, usuarios, etc.).

Modelos custom `App\Models\Role` y `App\Models\Permission` que extienden los de Spatie, registrados en `config/permission.php`.

#### Reglas implementadas (todas testeadas)

| Regla | Dónde se aplica | Test |
|---|---|---|
| **Solo `superadmin` puede asignar ámbito `ambos`** | `RolePolicy::puedeAsignarAmbito()` | `admin_no_puede_asignar_ambito_ambos` |
| **No puedes asignar nivel superior al tuyo** | `Roles\Index::guardar()` | `no_se_puede_asignar_nivel_superior_al_propio` |
| **Delegación: solo permisos que tú mismo tienes** | `RoleForm::permisosAsignablesPor()` | `admin_solo_puede_delegar_permisos_que_tiene` |
| **Compatibilidad de ámbito**: permisos móviles no se guardan en rol web | `RoleForm::save()` filtra antes de `syncPermissions` | `permisos_movil_no_se_guardan_en_rol_web` |
| **Roles del sistema** (`es_sistema=true`) protegidos | `RolePolicy::update/delete()` | `no_se_puede_eliminar_un_rol_del_sistema`, `admin_no_puede_editar_un_rol_del_sistema` |
| **`superadmin` nunca editable** | `RolePolicy::update()` | `nadie_puede_editar_el_rol_superadmin` |
| **Roles del sistema no renombrables** | `RoleForm::save()` ignora `name` si `es_sistema` | `superadmin_puede_editar_un_rol_del_sistema_pero_no_renombrarlo` |
| **Cambio de ámbito resetea permisos** | `Roles\Index::updatedFormAcceso()` + modal de confirmación | 3 tests del flujo (abrir modal, confirmar, cancelar) |
| **Scoping por nivel en listado** | Query `where('nivel', '<=', $nivelActual)` | `admin_no_ve_el_rol_superadmin_en_el_listado` |

#### UX cuidada

- **Permisos agrupados por categoría** con título collapsable y badge de ámbito por cada permiso.
- **Checkbox cabecera "marcar todos"** en cada categoría con tri-state (`all`/`some`/`none`) usando `<input indeterminate>` controlado por Alpine.
- **Permisos ordenados dentro de cada categoría**: web → movil → ambos (con name como tie-breaker).
- **Descripciones inequívocas** para permisos complementarios: `albaranes.ver_todos` → *"Ver albaranes de TODA la empresa (no solo los propios)"*; `albaranes.ver_propios` → *"Ver SOLO los albaranes en los que participo"*. Aplicado igual a ausencias e incidencias.
- **Modal de confirmación al cambiar ámbito** si hay permisos previos: *"Cambiar a X eliminará los Y permisos actuales"*.
- **Filtros**: Tipo (sistema/personalizados) + Ámbito (web/movil/ambos) + búsqueda por nombre.

#### Métricas del bloque

- **26 tests** feature (24 originales + 2 nuevos para toggle de categoría + 1 para orden por ámbito).
- 9 archivos nuevos · 4 modificados.

---

### 🧹 Refactor de nombres en tres oleadas

Durante el cierre de Fase 1 se detectó que las tablas se llamaban con nombres largos (`empresas_clientes`, `configuracion_empresa`) y la FK `empresa_cliente_id` era verbosa. El cliente decidió consolidar nombres más cortos antes de Fase 2.

#### Oleada 1 — Renombrado de tablas en BD

Dos migraciones nuevas (sin tocar las originales para preservar el orden histórico):

- `2026_05_13_101500_update_empresas_clientes_add_numero_cliente_remove_correo_notificaciones.php` — añade `numero_cliente` (único, autogenerado al crear), elimina `correo_notificaciones`.
- `2026_05_13_120000_rename_configuracion_empresa_and_empresas_clientes.php` — `Schema::rename`:
  - `empresas_clientes` → `clientes`
  - `configuracion_empresa` → `empresa`

#### Oleada 2 — Renombrado completo en código

Los modelos `EmpresasCliente` y `ConfiguracionEmpresa` se renombraron a `Cliente` y `Empresa`. **Refactor amplio (~30 archivos)** con coherencia 100%:

| Tipo | Antes | Ahora |
|---|---|---|
| Modelo | `EmpresasCliente`, `ConfiguracionEmpresa` | `Cliente`, `Empresa` |
| Policy | `EmpresasClientePolicy`, `ConfiguracionEmpresaPolicy` | `ClientePolicy`, `EmpresaPolicy` |
| Form Livewire | `EmpresasClienteForm`, `ConfiguracionEmpresaForm` | `ClienteForm`, `EmpresaForm` |
| Livewire Component | `App\Livewire\ConfiguracionEmpresa\Edit` | `App\Livewire\Empresa\Edit` |
| Factory | `EmpresasClienteFactory` | `ClienteFactory` |
| Blade view dir | `livewire/configuracion-empresa/` | `livewire/empresa/` |
| Test dir | `tests/Feature/ConfiguracionEmpresa/` | `tests/Feature/Empresa/` |
| Relación PHP | `$user->empresaCliente`, `$proyecto->empresaCliente` | `$user->cliente`, `$proyecto->cliente` |
| Sidebar key | `configuracion_empresa` | `empresa` |

#### Oleada 3 — Renombrado de la columna FK

Tercera migración (`2026_05_13_130000_rename_empresa_cliente_id_to_cliente_id.php`):
- `users.empresa_cliente_id` → `users.cliente_id`
- `proyectos.empresa_cliente_id` → `proyectos.cliente_id`

Actualizadas todas las referencias en modelos (fillable + relaciones limpias sin segundo argumento), forms, livewire components, blade views, factories, seeder, tests.

#### Bug detectado y arreglado durante el refactor

En `Usuarios\Index.php` se importaba `Spatie\Permission\Models\Role` en vez de `App\Models\Role`. Eso impedía a Larastan ver el campo custom `acceso` del rol. Cambiado a `App\Models\Role`.

---

### 💬 Aclaraciones del modelo (decisiones documentadas)

Durante la sesión surgieron dos preguntas conceptuales del cliente que vale la pena documentar:

#### 1. ¿Para qué sirve `nivel` actualmente?

El `nivel` es la **jerarquía militar** del sistema. Cinco reglas vivas en código:

1. **Listado de usuarios** scoped: no ves usuarios con rol de nivel > el tuyo.
2. **Listado de roles** scoped: no ves roles con nivel > el tuyo.
3. **Asignación de roles**: el select solo muestra roles de nivel ≤ tuyo.
4. **Editar/eliminar usuarios o roles**: requiere tu nivel ≥ nivel del target.
5. **Crear roles**: el `nivel` del rol nuevo ≤ tu nivel.

Niveles actuales: superadmin=100, administrador=50, trabajador=10, responsable=10.

**Tres puertas cerradas contra auto-escalada de nivel**:
- A: Editar el rol del sistema "administrador" → bloqueado por `es_sistema`.
- B: Crear un rol custom con nivel superior → bloqueado por validación.
- C: Asignarse a sí mismo un rol superior → no aparece en el select por jerarquía.

#### 2. ¿`albaranes.ver_todos` no duplica el checkbox "marcar todos" de la cabecera?

No, son cosas distintas:

- **`albaranes.ver_todos`** es un PERMISO de negocio: decide si el usuario ve los albaranes de toda la empresa o solo los suyos. Va en pareja con `albaranes.ver_propios`.
- **Checkbox cabecera "ALBARANES"** es solo una utilidad UI para marcar/desmarcar todos los permisos de esa categoría de golpe.

Las descripciones se reescribieron para que no haya ambigüedad incluso leyendo cada permiso en aislamiento.

---

## 📊 Métricas finales (Fase 1 cerrada + extensión)

```
Pint:             ✅ passed
Larastan:         ✅ 45/45 sin errores
PHPUnit:          ✅ 126 passed · 447 assertions · ~40 s
migrate:fresh:    ✅ OK con seeders demo

Migraciones:                11 (8 originales + 3 de refactor: numero_cliente, rename tablas, rename FK)
Modelos Eloquent:           12 (User + 9 dominio + Role + Permission custom)
Policies registradas:        9 (las 8 anteriores + RolePolicy)
Form Objects Livewire:       9 (los 8 anteriores + RoleForm)
Componentes Livewire:        8 (los 7 anteriores + Roles\Index)
Componentes Blade UI:       18
Rutas web protegidas:        8 (Dashboard · Clientes · Proyectos · Materiales · Materiales/{id}/Lotes · Usuarios · Conceptos · Empresa · Roles)
Permisos catalogados:       54 (clasificados por ámbito + categoría + descripción)
Tests feature:             126 (98 cierre Fase 1 + 22 CRUD Roles + 2 toggle + 1 orden + 3 ajustes refactor)
```

---

## 🎨 Decisiones de diseño confirmadas en esta iteración

19. **Permisos extensibles**: el catálogo de 54 permisos es un punto de partida. Cada fase nueva añadirá los suyos al método `catalogoPermisos()` del seeder.
20. **Ámbito (`ambito`) en permissions** como columna nativa de Spatie extendido, con valores `web|movil|ambos`. Se filtra dinámicamente en el UI del Rol según el ámbito elegido para el rol.
21. **Regla de delegación universal**: nadie puede asignar permisos ni roles ni niveles que él mismo no tenga. Implementada en `RoleForm::permisosAsignablesPor()` y en `UserPolicy::puedeAsignarRol()`.
22. **Cambio de ámbito = reset de permisos** (opción C de las tres evaluadas): el más simple, evita estados intermedios ambiguos. Mostramos modal de confirmación obligatorio.
23. **Checkbox tri-state** con Alpine y `wire:key` dinámico que incluye el estado: cada vez que cambia el conjunto de permisos, Alpine re-evalúa `$el.indeterminate` y `$el.checked` desde el `data-state` que renderiza el servidor. Sin código JS adicional.
24. **Roles del sistema protegidos a doble nivel**: la Policy bloquea su edición y el Form ignora cambios de `name` aunque pasen los gates (cinturón + tirantes).
25. **superadmin = rol blindado**: ni siquiera otro superadmin puede editarlo. Es el "kernel" del sistema de permisos.
26. **Nombres cortos en BD** (`clientes`, `empresa`, `cliente_id`) más legibles que los originales y alineados con el lenguaje del negocio. El refactor pre-Fase 2 evita arrastrar nombres legacy durante el desarrollo de albaranes.

---

## 🛣 Hoja de ruta inmediata

```
✅ FASE 1 ── 100 % + extensión ─────────────────────────
   ✅ Toda la base de datos + UI base
   ✅ 8 CRUDs operativos (incluido Roles personalizados)
   ✅ Refactor de nombres consolidado
   ✅ 54 permisos clasificados
─────────────────────────────────────────────────────────
   ⏭️ FASE 2 — Albaranes core + firma (3 sem estimadas)
   ⏭️ FASE 3 — Albarán personalizado (2-3 sem)
   ⏭️ FASE 4 — Ausencias e incidencias (2-3 sem)
   ⏭️ FASE 5 — Reportes y exportación (2 sem)
```

**Tarea menor anotada para Fase 4** (si el cliente la requiere): regla *"Acceso por defecto al asignar usuario"* — al elegir un rol para un usuario, el `acceso` se hereda automáticamente del rol elegido. Actualmente la app ya cumple esto porque el `acceso` del User se eliminó y se lee siempre del rol asignado, pero podría haber una variante de "override puntual" si se necesitase.

---

## 📝 Notas finales

- La extensión post-cierre añade **complejidad muy bien encapsulada**: toda la lógica nueva vive en `RolePolicy`, `RoleForm`, `Roles\Index`. El resto del proyecto no se ve afectado.
- El refactor de nombres fue **caro pero correcto en el momento ideal**: pre-producción, con todos los tests pasando antes y después. Hacerlo en Fase 2+ habría sido el doble de trabajo.
- **Próxima sesión**: arrancar Fase 2 (Albaranes core). Empezamos por la migración `albaranes` + tablas de líneas (personal y material) + tabla `albaran_firmas`, luego el form Livewire móvil, luego el flujo de firma con token email.

---

**Iteración 5 cerrada. Fase 1 100% lista para Fase 2.**
