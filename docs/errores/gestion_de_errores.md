# 🐞 Gestión de errores en ELECIND

> **Vigente desde:** 2026-05-20 (tras instalar `laravel/telescope`).
> **Propósito:** unificar cómo detectamos, diagnosticamos y arreglamos errores —
> sea en desarrollo o reportados por el usuario. Evitar parches a ciegas.

---

## 1. Regla de oro: diagnosticar primero, arreglar después

**Antes de tocar código**, hay que **confirmar el punto exacto donde algo falla**.
Adivinar y cambiar líneas "a ver si va" deja errores latentes y crea nuevos bugs.

Orden estricto:

1. **Reproducir** el error de forma consistente.
2. **Instrumentar** (Telescope, `Log::info`, breakpoints) hasta saber qué línea/condición lo dispara.
3. **Entender la causa raíz** (no el síntoma).
4. **Arreglar** el origen, no el síntoma.
5. **Confirmar** que el caso roto ya pasa Y que no rompimos otros casos próximos.

Si el atajo es "borrar y rehacer", primero documenta qué falla. Cuesta menos
arreglarlo bien que vivir con el atajo durante meses.

---

## 2. Herramientas disponibles (qué usar para qué)

| Herramienta | Cuándo |
|---|---|
| **Laravel Telescope** (`/telescope`) | Cualquier cosa que pase por el servidor: Livewire, queries, excepciones, mails, jobs. **Primera parada para cualquier bug.** |
| **`php artisan pail`** | Ver `storage/logs/laravel.log` en vivo en la consola. Útil cuando no quieres abrir Telescope o estás depurando un job. |
| **`Log::info(...)`, `Log::warning(...)`, `Log::error(...)`** | Cuando quieres trazas explícitas a tu medida. Quedan en el log y aparecen también en Telescope > Logs. |
| **`dd($var)`, `dump($var)`** | Pause-and-print en mitad del request. Útil para Livewire y controladores. **No dejarlo en commits.** |
| **DevTools del navegador** | Errores JS, peticiones de red (Livewire usa POST), CSS roto, Alpine que falla en consola. |
| **Página de error de Laravel (Collision/Ignition)** | Por defecto sale en local con stack trace. Léela. No te asustes del rojo. |

> **Acceso a Telescope:** `http://localhost/.../public/telescope` estando logueado en local.
> Solo activo cuando `APP_ENV=local`. En producción ni se carga.

---

## 3. Flujo estándar ante un bug

```
┌─────────────────────────────────────────────────────┐
│  1. Reproducir el error en local                    │
│     ↓                                               │
│  2. Abrir /telescope en otra pestaña                │
│     ↓                                               │
│  3. Provocar el error                               │
│     ↓                                               │
│  4. En Telescope ir a la pestaña relevante:         │
│       · Livewire   → si es un wire:click/wire:model │
│       · Exceptions → si vimos pantalla de error     │
│       · Queries    → si va lento o algo no carga    │
│       · Requests   → si es una navegación normal    │
│       · Mail       → si es un email                 │
│     ↓                                               │
│  5. Localizar la entrada → leer payload + trace     │
│     ↓                                               │
│  6. Si no basta: añadir Log::info(...) en el código │
│       sospechoso y volver a reproducir              │
│     ↓                                               │
│  7. Confirmada la causa → escribir el fix mínimo    │
│     ↓                                               │
│  8. Reproducir → ya no falla → casos vecinos OK     │
│     ↓                                               │
│  9. Borrar logs temporales / dd() / dump() que pusi-│
│       mos para depurar                              │
└─────────────────────────────────────────────────────┘
```

---

## 4. Casos típicos y dónde mirar

### 4.1 "Pulso un botón Livewire y no pasa nada"

**Mirar:** Telescope → **Livewire**.

- Si **no aparece ninguna entrada** al pulsar el botón → el evento ni siquiera salió del navegador. Posibles causas:
  - **BOM UTF-8** en el blade (Livewire no monta el componente). Solución: reabrir el blade y guardar como UTF-8 sin BOM.
  - **Alpine duplicado** ejecutándose sin `$wire`. Solución: nunca arrancar Alpine aparte en `app.js` (Livewire lo monta).
  - Error JS bloqueante en consola → arreglarlo y reintentar.
- Si **sí aparece** la entrada pero el método no hizo lo que querías → mirar el payload, el response y los datos modificados. Quizás el método se llama distinto, falla una validación silenciosa, o un policy lo deniega.

### 4.2 "Veo una pantalla blanca / error 500"

**Mirar:** Telescope → **Exceptions**. Hay stack trace completo, con archivo, línea y SQL si la causa era una query.

Si la página de Laravel (Collision) sale con stack: léela. La primera línea **que es de tu código** (no de vendor) suele ser donde está el problema.

### 4.3 "Va lento"

**Mirar:** Telescope → **Queries** dentro de la petición lenta.

Síntomas típicos:
- **N+1**: 30 consultas casi idénticas en un listado. Solución: `->with(['relacion'])` en el query del controlador / componente.
- **Query sin índice**: una sola query pero tarda 2s. Solución: añadir índice en la BD.
- **`SELECT *` de tabla enorme**: traes columnas innecesarias. Solución: `->select(['id','nombre'])`.

### 4.4 "El email no llega"

**Mirar:** Telescope → **Mail**.

- Si la entrada está → el email se generó. El fallo es del SMTP/transport o el `MAIL_MAILER=log` (en local va a log por defecto).
- Si **no** está → el código nunca llegó a `Mail::send(...)`. Mirar **Exceptions** o **Livewire** para ver si la acción que debía enviar el email falló antes.

### 4.5 "Un select Livewire pierde el valor al re-render"

**Mirar:** Telescope → **Livewire** payload del re-render. Compara `data` antes y después.

Causa típica documentada en este proyecto: componentes Alpine custom que no
re-leen `$wire.get(propiedad)` tras re-render → quedan "solo escritura". Solución:
hacer `selected` un *getter* que lee de `$wire`, no una variable local.

### 4.6 "Hago Index → Crear y el formulario sale con datos viejos"

Síntoma: navegas con `wire:navigate` y aparecen datos del anterior componente.
Causa documentada: el `wire:key` se aplicaba a props del Form en lugar de al root del nuevo componente.

**Mirar:** Telescope → **Livewire** → entrada del crear; ¿el componente se montó con `mount()`?
Solución: poner `wire:key` único en el root del componente nuevo y usar `wire:navigate.fresh` si hace falta.

### 4.7 "Los tests fallan masivamente"

Si **muchos** tests fallan a la vez tras un cambio, casi nunca es bug en tus tests
— suele ser una **migración rota**. Ej. real: una migración con `SHOW INDEX`
(MySQL-only) revienta bajo SQLite que es el driver de tests.

**Mirar:** salida de `php artisan test`. La primera excepción de migración te
señala el archivo. Reescribir la migración en SQL portable (usar Schema/Doctrine).

---

## 5. Patrones de manejo de errores que usamos en la app

### 5.1 Validación all-or-nothing en operaciones en lote

**Cuándo:** importaciones, asignaciones masivas.
**Patrón:** validar TODO antes de guardar. Si hay un solo fallo, no se guarda nada
y se listan **todos** los errores juntos en una tabla `Fila · Campo · Motivo`.

Ejemplo: `app/Livewire/Clientes/Importar.php` (importación de clientes).

```php
$erroresLocal = [];
foreach ($filas as $i => $datos) {
    $validador = Validator::make($datos, $reglas, $mensajes);
    if ($validador->fails()) {
        foreach ($validador->errors()->messages() as $campo => $mensajes) {
            $erroresLocal[] = [
                'fila'    => $numeroLinea,
                'columna' => $this->camposDisponibles()[$campo] ?? $campo,
                'motivo'  => $mensajes[0],
            ];
        }
    }
    // … checks adicionales (unicidad, duplicados dentro del archivo) …
}

// Regla de oro: si hay UN solo error, no se guarda NADA.
if ($erroresLocal !== []) {
    $this->errores = $erroresLocal;
    return;
}

DB::transaction(function () use ($aCrear) {
    foreach ($aCrear as $datos) {
        Cliente::create($datos);
    }
});
```

### 5.2 Errores por campo en formularios Livewire

**Cuándo:** validación de un único registro (Crear/Editar).
**Patrón:** `addError('campo', 'mensaje en español')` y mostrarlo junto al input.

```php
public function guardar(): void
{
    if (Cliente::where('codigo_cliente', $this->form->codigo_cliente)->exists()) {
        $this->addError(
            'form.codigo_cliente',
            "El código {$this->form->codigo_cliente} ya existe. Escribe otro número."
        );
        return;
    }

    $this->form->save();
    session()->flash('status', 'Cliente guardado correctamente.');
}
```

En el blade:
```blade
<x-ui.field label="Código" :error="$errors->first('form.codigo_cliente')">
    <x-ui.input wire:model="form.codigo_cliente" />
</x-ui.field>
```

### 5.3 Mensajes flash globales (éxito/error)

**Cuándo:** confirmar al usuario que algo se guardó/falló.
**Patrón:** `session()->flash('status', ...)` o `session()->flash('error', ...)`.
El layout `web.blade.php` ya tiene `<x-ui.flash />` global → **no duplicar** flash
locales en cada vista (esto causó la incidencia "doble notificación" en Ajustes/Empresa).

```php
session()->flash('status', "Cliente «{$cliente->nombre}» actualizado.");
$this->redirectRoute('clientes.editar', $cliente);
```

### 5.4 Try/catch con mensaje amable vs dejar burbujear

- **Burbujear (no capturar)**: errores realmente inesperados (BD caída, archivo corrupto, fallo del servidor). La página de error de Laravel los recoge y Telescope los registra.
- **Capturar**: cuando puedes dar un mensaje útil al usuario. Ej. CSV mal formado en importación, archivo demasiado grande, etc.

```php
try {
    $filas = $this->leerHoja();
} catch (\Throwable $e) {
    \Log::error('Error leyendo xlsx', ['mensaje' => $e->getMessage()]);
    $this->addError('archivo', 'No se ha podido leer el archivo. ¿Está corrupto?');
    return;
}
```

> **No tragues excepciones en silencio.** Si capturas, deja log (`Log::error`)
> y muestra algo al usuario. Tragarlas vacía la pista hacia la causa.

### 5.5 Soft delete, nunca destrucción

Toda eliminación es **soft delete** (`$model->delete()` con `SoftDeletes`). El
listado puede mostrar la papelera con un filtro. `restore()` los recupera.
Nunca hacemos `forceDelete()` salvo migraciones explícitas.

### 5.6 Snapshot al borrar usuarios

Si un usuario se elimina, los albaranes/firmas conservan los datos visibles en
una columna `snapshot_data` (JSON). Trazabilidad legal completa aunque el
usuario ya no exista.

### 5.7 Idioma de los mensajes

**Siempre en español, dirigidos al usuario, no técnicos.**

- ❌ `"SQLSTATE[23000]: Integrity constraint violation"`
- ✅ `"El código 100 ya existe. Escribe otro número."`

Las reglas centralizadas en `ClienteFields::getValidationRules()` van con
mensajes en `ClienteForm::messages()`. Reutilizar esa fuente, no duplicar.

---

## 6. Anti-patrones a evitar

| ❌ Mal | ✅ Bien |
|---|---|
| Borrar/regenerar el archivo "a ver si va" | Reproducir → Telescope → leer trace |
| `try { ... } catch (\Throwable $e) {}` vacío | `catch` con `Log::error` + mensaje útil al usuario |
| Mostrar `$e->getMessage()` al usuario final | Mensaje amable en español, detalle al log |
| `dd()` o `Log::info` dejados en producción | Limpiar antes del commit |
| Flash global + flash local en la misma vista | Solo el global del layout (`<x-ui.flash />` en `web.blade.php`) |
| Validar en frontend solamente | Siempre validar también server-side (Policies + Form requests + Validator) |
| Hardcodear reglas de validación en dos sitios | Centralizar en `ClienteFields`/`AjustesFields` y leer desde ahí |
| Tragar excepciones de carga de archivos | `try/catch` con mensaje + `Log::error` |
| `php artisan view:clear` suelto en XAMPP | `php artisan view:cache` tras tocar blades |
| Comprobar unicidad solo contra BD en imports | Comprobar también **dentro del propio archivo** (lote) |

---

## 7. Incidencias reales documentadas (cómo las detectaríamos hoy con Telescope)

| Incidencia | Síntoma original | Causa real | Dónde la cazaríamos hoy |
|---|---|---|---|
| **BOM UTF-8 en Empresa** | Botones Guardar/Deshacer no hacían nada | Blade guardado con BOM EF BB BF; Livewire no montaba | Telescope → Livewire: NINGUNA entrada al pulsar → BOM o JS roto |
| **Doble Alpine** | Selects que dejaban de funcionar tras navegar | `app.js` arrancaba un 2º Alpine sin `$wire` | Consola del navegador + Telescope > Livewire: requests sí, payload incorrecto |
| **wire:navigate morph bleed** | "Creo cliente, no guarda" | `wire:key` se filtraba a props del Form | Telescope → Livewire: el `mount()` no se ejecutó al navegar |
| **Tabla `borradors`** | `Table borradors not found` | Laravel pluralizó "Borrador" → `borradors` | Telescope → Exceptions: error SQL con nombre de tabla |
| **Selects solo escritura** | Valor no aparece tras editar | Variable local en lugar de getter `$wire.get(...)` | Telescope → Livewire: data en payload OK, pero UI no la refleja |
| **Suite tests rota** | 209 fail / 8 pass de golpe | Migración con `SHOW INDEX` (MySQL) bajo SQLite | Salida de `php artisan test`: primera excepción señala el archivo |
| **Importación con max viejos** | Filas válidas en alta rechazadas al importar | Reglas hardcodeadas duplicadas en Importar | Telescope → Livewire: ver reglas aplicadas en la validación |
| **CIF unique en import** | Importar bloquea CIF duplicado pese a quitarlo en alta | Comprobación manual en Importar quedó obsoleta | Lectura del código + comparativa contra `ClienteFields::uniqueFields()` |

---

## 8. Checklist rápido para "no funciona X"

Antes de avisar a otro o tocar código:

- [ ] ¿Lo reproduzco yo?
- [ ] ¿Telescope → Livewire muestra la entrada al pulsar?
  - [ ] Si **no**: ¿hay BOM en el blade? ¿hay error JS en consola?
  - [ ] Si **sí**: ¿el método se ejecutó? ¿hay excepción en la entrada?
- [ ] ¿Telescope → Queries muestra la query que esperaba?
- [ ] ¿Telescope → Exceptions tiene algo en los últimos 5 minutos?
- [ ] ¿`storage/logs/laravel.log` tiene algo nuevo? (`php artisan pail`)
- [ ] ¿La validación dejó pasar lo que debería bloquear (o al revés)?
- [ ] ¿Las reglas vienen de la fuente única (`*Fields`) o están duplicadas?
- [ ] ¿Hay un `wire:key` que falte o sobre?
- [ ] ¿Se ejecutó `npm run build` tras tocar `app.js`?
- [ ] ¿Se ejecutó `php artisan view:cache` tras tocar un Blade en XAMPP?

Si después de eso sigue sin verse el porqué, instrumenta con `Log::info('punto A', compact('var1','var2'))` en los puntos sospechosos y vuelve a reproducir mirando Telescope → Logs.

---

## 9. Apéndice: comandos útiles

```bash
# Ver logs en vivo en la consola
php artisan pail

# Limpiar entradas viejas de Telescope (mantiene últimas 48h)
php artisan telescope:prune --hours=48

# Recompilar vistas (NUNCA usar `view:clear` suelto en XAMPP)
php artisan view:cache

# Reconstruir assets JS/CSS tras tocar app.js, package.json, etc.
npm run build

# Listar todas las rutas (filtrar por nombre)
php artisan route:list --name=clientes

# Lanzar tests
php artisan test

# Verificar análisis estático y formato
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
```

---

## 10. Convenciones de este documento

- Si añades una incidencia nueva al apartado 7, sigue el patrón
  `Síntoma · Causa real · Dónde se caza`.
- Si descubres un anti-patrón nuevo, añádelo al apartado 6.
- Si cambia el stack de herramientas (p. ej. se instala log-viewer o Pulse en
  producción), actualiza el apartado 2.
