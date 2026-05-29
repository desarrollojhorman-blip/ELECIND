# Idea / diseño — Rol "Jefe de equipo" (29/05/2026)

Documento de diseño (aún NO implementado). Recoge el objetivo del rol, el modelo conceptual acordado y el plan de gestión. Sirve de guía para cuando se construya.

---

## 1. Objetivo del rol

El **Jefe de equipo** es un rol intermedio cuya misión es **ayudar al administrador a gestionar los borradores y albaranes de un cliente concreto** (normalizar/convertir los partes personalizados que los trabajadores mandan desde el móvil, editar albaranes, decidir si se firman, etc.).

La idea base: **que solo tenga acceso a los datos del/los cliente(s) que se le asignen.** No es un administrador global; es un gestor acotado a sus clientes.

- **Acceso: solo web.** Es trabajo de gestión (pantalla grande), no de campo. El panel móvil (`/m`) es para operarios. Nota: "solo web" no impide abrir la web desde el navegador del móvil (es responsive); solo no le da el panel `/m` dedicado.
- **Nivel sugerido: 30** (entre trabajador=10 y administrador=50): gestiona por debajo del administrador y no puede tocar admins ni superadmins.

---

## 2. El modelo conceptual: DOS capas independientes

Toda la lógica del rol se entiende separando dos cosas que **no** son lo mismo:

### Capa A — Permisos: *qué acciones* puede hacer
Lo que se configura en la pantalla **Roles y permisos**: "crear albaranes", "editar", "imprimir", "solicitar firma"… Son las **llaves**. Configurables por rol.

### Capa B — Scoping: *sobre qué datos* actúan esas acciones
"Este usuario solo ve/toca los datos de sus clientes." Es un **filtro** que se aplica por debajo, en código. **NO es un permiso de la pantalla de Roles** — es una característica del rol.

> **Analogía**: el permiso es la *llave* que abre la puerta "albaranes"; el scope es el *edificio* al que esa llave te deja entrar (solo el de tu cliente). Llave y edificio son cosas distintas y se combinan.

---

## 3. Concepto clave: datos GLOBALES vs datos SCOPED

No todas las entidades se comportan igual ante el scoping:

| Entidad | ¿Tiene `cliente_id`? | Comportamiento para el jefe de equipo |
|---|---|---|
| **Proyectos** | ✅ Sí | SCOPED: solo los de sus clientes |
| **Albaranes** | ✅ Sí | SCOPED: solo los de sus clientes |
| **Borradores** | ✅ Sí | SCOPED: solo los de sus clientes |
| **Conceptos** | ❌ No (global) | NO se filtra. Catálogo compartido. Puede verlos/crearlos (según permiso) |
| **Materiales** | ❌ No (global) | NO se filtra. Catálogo compartido. Puede verlos/crearlos (según permiso) |
| **Clientes** | — | Solo ve los asignados. **NO crea** (ver paradoja abajo) |
| **Usuarios** | internos sin cliente | Fuera del MVP: no gestiona usuarios |

### La paradoja de "crear cliente"
Si el jefe solo ve sus clientes asignados y crea uno nuevo, ese cliente **no estaría asignado a él → no lo vería**. Por eso **crear clientes no encaja** con un rol scoped. No es un fallo técnico: es una **combinación de permisos incoherente**. Solución: no darle ese permiso (y, opcionalmente, un aviso en la UI si alguien lo marca).

### "Dar de alta desde borradores"
Al normalizar un borrador, el jefe puede necesitar crear **conceptos/materiales** reales. Eso **sí se permite** sin contradicción, porque son **globales** (no "de un cliente"). Lo que no encaja es crear **clientes** (scoped → paradoja).

---

## 4. Cómo se gestiona — plan acordado

### 4.1 "Jefe de equipo" = ROL DE SISTEMA (no rol personalizado)
La petición "permisos predefinidos y no modificables" **ya existe en el sistema**: se llama **rol de sistema** (como `administrador`). Sus permisos los fija el seeder y la pantalla de Roles no deja editarlos.

Por tanto, en vez de construir un mecanismo nuevo de "check que bloquea permisos", se hace **Jefe de equipo como rol de sistema**:
- Permisos **predefinidos en el seeder** → no modificables por el administrador.
- Nombre interno no renombrable ni borrable (ya protegido para roles de sistema).
- **Válvula de escape**: el **superadmin SÍ puede** ajustar los permisos de un rol de sistema si alguna vez hace falta (comportamiento ya existente). → coherencia en el día a día + flexibilidad puntual.

### 4.2 Flag de scoping en el rol: `solo_clientes_asignados`
- Nuevo campo booleano en `roles`. Activa la Capa B (scoping por cliente).
- Para "Jefe de equipo" viene **marcado de fábrica**.
- Queda **disponible como check** en el formulario de roles personalizados, por si en el futuro se quieren otros perfiles limitados por cliente ("Jefe de obra", "Comercial de zona"…). En ese caso sus permisos serían libres (+ avisos para combinaciones raras).
- El check aplica **solo** a las entidades con `cliente_id` (proyectos, albaranes, borradores). **No** intenta scopear usuarios ni catálogos (ahí está la sobre-ingeniería que se descarta).

### 4.3 Asignación de clientes al usuario: relación N:M
- Un jefe podrá tener **uno o varios clientes** (se decidió N:M desde el principio para no rehacer la BD después).
- **Tabla pivote nueva** (p. ej. `cliente_user_gestion`) **solo para los clientes que gestiona el jefe**, sin tocar `users.cliente_id` (que se sigue usando para los externos = 1 cliente). Así no se rompe la lógica actual de externos.
- En el **formulario de usuario**: cuando el rol asignado tenga el flag `solo_clientes_asignados`, aparece un **multi-select de clientes obligatorio** ("sus clientes").

### 4.4 Scoping en código (la Capa B)
- Helper en `User`, p. ej. `clientesGestionados()` → devuelve los IDs de sus clientes si su rol es scoped, o `null` (ve todo) si no.
- En cada listado (borradores, albaranes, proyectos):
  `->when($user->clientesGestionados(), fn ($q, $ids) => $q->whereIn('cliente_id', $ids))`.
- **Filtrado explícito** (no Global Scope mágico): más predecible y fácil de auditar; evita efectos colaterales en seeders/admin.

### 4.5 Permisos previstos para el Jefe de equipo (Capa A)
Borrador inicial (se afina al implementar):
- `borradores.ver_todos`, `borradores.convertir`, `borradores.modificar`
- `albaranes.ver_todos`, `albaranes.modificar`, `albaranes.crear_web`, `albaranes.imprimir`, `albaranes.solicitar_firma`, `albaranes.facturar` *(según se confirme)*
- Lectura de catálogos para normalizar: `clientes.ver` (solo verá los suyos), `proyectos.ver`, `conceptos.ver`, `materiales.ver`
- Opcional: `conceptos.crear`, `materiales.crear` (globales) si debe dar de alta al normalizar
- **NO**: gestión de usuarios, crear clientes, empresa/ajustes/roles/api/logs

---

## 5. Por qué este enfoque (resumen de la decisión)

- **Permisos fijos "no modificables"** → se consigue haciéndolo **rol de sistema** (mecanismo existente y probado), no construyendo un "check que bloquea permisos" (que duplicaría el rol de sistema y habría que mantener).
- **"Solo ve sus clientes"** → lo resuelve el **flag de scoping** + tabla N:M + filtrado en código.
- Son **dos herramientas separadas**, cada una para su cosa. Acoplarlas complicaría sin aportar.
- Se evita la **sobre-ingeniería**: un único flag de scoping (no toggles por entidad), y las combinaciones de permisos incoherentes se evitan **no asignándolas** (con aviso opcional), no con más lógica.

---

## 6. Decisiones aún ABIERTAS (confirmar antes de implementar)

1. **Camino del rol**: ¿Jefe de equipo como **rol de sistema** (recomendado, un solo perfil estándar) o como **rol personalizado con el check** (si se prevén varios perfiles scoped distintos)? → Recomendación: rol de sistema ahora + dejar el check disponible para el futuro.
2. **Permisos sobre albaranes**: ¿solo ver + convertir borradores, o también editar/imprimir/solicitar firma/facturar? (la conversación apunta a que **sí** pueda editar y gestionar firmas).
3. **Conceptos/materiales**: ¿solo lectura, o también **crear** (para dar de alta al normalizar)?
4. **Aviso en UI** para combinaciones incoherentes (p. ej. check scoping + permiso de crear clientes): ¿lo añadimos o se omite?

---

## 7. Plan de implementación por fases (cuando se confirme)

1. **Fase 1 — Rol + scoping base**
   - Migración: campo `solo_clientes_asignados` en `roles` + tabla pivote `cliente_user_gestion`.
   - Seeder: rol de sistema `jefe_de_equipo` (etiqueta "Jefe de equipo", acceso web, nivel 30, flag scoping ON, permisos previstos).
   - Modelo `User`: relación N:M `clientesGestionados()` + helper de scope.
2. **Fase 2 — Formulario de usuario**
   - Multi-select de clientes (obligatorio) cuando el rol tenga el flag.
3. **Fase 3 — Scoping en listados**
   - Filtrar borradores, albaranes y proyectos por los clientes del jefe.
   - Verificar que el admin/superadmin siguen viendo todo (el scope solo aplica a roles con el flag).
4. **Fase 4 — Check en roles personalizados (opcional/futuro)**
   - Mostrar el check `solo_clientes_asignados` en el formulario de roles + aviso de combinaciones raras.

---

## 8. Estado

🟡 **Diseño acordado, pendiente de confirmación de las 4 decisiones abiertas (sección 6) para empezar la Fase 1.** No se ha tocado código todavía.
