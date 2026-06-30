# Resumen de cambios — Fase 2: Control de usuarios, materiales y vinculaciones

**Fecha:** 21/05/2026  
**Rama:** main

---

## 1. Páginas dedicadas para Materiales (Ver y Editar)

Hasta ahora los materiales se gestionaban desde un modal dentro del listado. Se migraron a páginas propias con tabs, igual que Clientes y Usuarios.

### Archivos creados
- `app/Livewire/Materiales/Ver.php`
- `app/Livewire/Materiales/Editar.php`
- `resources/views/livewire/materiales/ver.blade.php`
- `resources/views/livewire/materiales/editar.blade.php`

### Estructura de las páginas
Ambas páginas tienen tres tabs:
- **Material** — campos del registro (Nº Pedido, Familia, Descripción, Unidad, Stock)
- **Albaranes** — tabla desplegable con albaranes donde aparece el material (`lineasMaterial`)
- **Proyectos** — tabla desplegable con proyectos vinculados vía pivot `material_proyecto`

### Rutas añadidas en `routes/web.php`
```php
Route::get('/materiales/crear', MaterialesEditar::class)
    ->middleware('can:materiales.crear')->name('materiales.crear');

Route::get('/materiales/{material}', MaterialesVer::class)
    ->middleware('can:materiales.ver')->name('materiales.ver');

Route::get('/materiales/{material}/editar', MaterialesEditar::class)
    ->middleware('can:materiales.modificar')->name('materiales.editar');
```
> `/materiales/crear` va **antes** de `/{material}` para evitar conflicto con el route model binding.

### Relación añadida al modelo
En `app/Models/Material.php` se añadió la relación `proyectos()` que faltaba:
```php
public function proyectos(): BelongsToMany
{
    return $this->belongsToMany(Proyecto::class, 'material_proyecto')->withTimestamps();
}
```

---

## 2. Papelera en el listado de Materiales

Se añadió la funcionalidad de papelera al listado de materiales, idéntica al patrón de Clientes.

### Cambios en `app/Livewire/Materiales/Index.php`
- `#[Url(as: 'papelera')] public bool $verPapelera = false;`
- `puedeVerPapelera()` computed — usa permiso `materiales.modificar` (no hay permiso específico de papelera para materiales)
- `totalPapelera()` computed — `Material::onlyTrashed()->count()`
- `render()` — alterna entre `Material::query()` y `Material::onlyTrashed()` según modo

### Cambios en `resources/views/livewire/materiales/index.blade.php`
- Checkbox de papelera visible solo si `puedeVerPapelera`
- Banner ámbar informativo cuando el modo papelera está activo
- Botones Ver/Editar cambiados de `wire:click` modal a `as="a" href wire:navigate`
- Botón Nuevo cambiado a enlace a `route('materiales.crear')`

---

## 3. Tabs con sort en páginas de Clientes y Usuarios

Se añadieron tablas con controles de ordenación (▲/▼/↕) y columna "Ir" con enlace a las páginas propias de cada entidad.

### Clientes — Ver y Editar
**Tabs:** Cliente | Albaranes | Proyectos | Usuarios (en este orden)

Archivos modificados:
- `app/Livewire/Clientes/Editar.php` — añadido `albaranesDelCliente()` computed, sort state para albaranes, proyectos y usuarios
- `resources/views/livewire/clientes/ver.blade.php` — tab Albaranes nuevo, sort en todas las tablas, enlace a `usuarios.ver`
- `resources/views/livewire/clientes/editar.blade.php` — ídem, tabs bloqueados en modo crear

### Usuarios — Ver y Editar
**Tabs:** Usuario | Albaranes | Proyectos (en este orden)

Archivos modificados:
- `app/Livewire/Usuarios/Editar.php` — sort state para albaranes y proyectos, methods `ordenarAlbaranes` / `ordenarProyectos`
- `resources/views/livewire/usuarios/ver.blade.php` — sort en todas las tablas, `variant="info"` en icon-buttons
- `resources/views/livewire/usuarios/editar.blade.php` — ídem

### Patrón de sort usado
```php
public string $ordenAlbaranes = 'fecha';
public string $dirAlbaranes = 'desc';

public function ordenarAlbaranes(string $campo): void
{
    if ($this->ordenAlbaranes === $campo) {
        $this->dirAlbaranes = $this->dirAlbaranes === 'asc' ? 'desc' : 'asc';
    } else {
        $this->ordenAlbaranes = $campo;
        $this->dirAlbaranes = 'asc';
    }
    unset($this->albaranesDelXxx); // invalida computed cache
}
```
Los computeds usan `Collection::sortBy` / `sortByDesc` con `SORT_NATURAL | SORT_FLAG_CASE`.

---

## 4. Permiso `usuarios.eliminar` para Administrador

El rol `administrador` no tenía el permiso `usuarios.eliminar`. Se añadió quitándolo de la lista de exclusiones en el seeder.

Archivo: `database/seeders/RolesAndPermissionsSeeder.php`

Se re-ejecutó el seeder:
```
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 5. Vinculaciones en el modal de Conceptos

Se mantiene el modal para Conceptos (no tiene suficientes relaciones para justificar página propia), pero al editar un concepto existente se muestran dos secciones desplegables al pie del modal.

### Relación añadida al modelo
En `app/Models/Concepto.php`:
```php
public function albaranes(): HasMany
{
    return $this->hasMany(Albaran::class);
}
```

### Computeds añadidos en `app/Livewire/Conceptos/Index.php`
- `albaranesDelConcepto()` — albaranes con `concepto_id = $form->id`, con proyecto y cliente
- `proyectosDelConcepto()` — proyectos via pivot `proyecto_concepto`, con cliente

### Vista `resources/views/livewire/conceptos/index.blade.php`
Dentro del modal de edición, debajo del formulario, aparecen dos bloques Alpine desplegables:

```html
<div x-data="{ abierto: false }">
    <div class="flex items-center justify-between">
        <h4>Albaranes vinculados <span>{{ count }}</span></h4>
        <button x-on:click="abierto = !abierto">
            <x-heroicon-o-chevron-down :class="abierto ? 'rotate-180' : ''" />
        </button>
    </div>
    <div x-show="abierto" x-cloak x-transition>
        <!-- tabla: Número | Fecha | Proyecto | Estado | enlace -->
    </div>
</div>
```

Mismo patrón para Proyectos (Proyecto | Código | Cliente | Estado | enlace).

El modal pasa de `size="md"` a `size="lg"` al editar (cuando `$form->id` existe).

---

## Resumen de archivos modificados/creados

| Archivo | Acción |
|---|---|
| `app/Models/Concepto.php` | Añadida relación `albaranes()` |
| `app/Models/Material.php` | Añadida relación `proyectos()` |
| `app/Livewire/Materiales/Ver.php` | Creado |
| `app/Livewire/Materiales/Editar.php` | Creado |
| `app/Livewire/Materiales/Index.php` | Papelera, navegación a páginas propias |
| `app/Livewire/Conceptos/Index.php` | Computeds de vinculaciones |
| `app/Livewire/Clientes/Editar.php` | Tab albaranes, sort |
| `app/Livewire/Usuarios/Editar.php` | Sort albaranes y proyectos |
| `resources/views/livewire/materiales/ver.blade.php` | Creado |
| `resources/views/livewire/materiales/editar.blade.php` | Creado |
| `resources/views/livewire/materiales/index.blade.php` | Papelera, navegación |
| `resources/views/livewire/conceptos/index.blade.php` | Tablas desplegables de vinculaciones |
| `resources/views/livewire/clientes/ver.blade.php` | Tab albaranes, sort, enlaces usuarios |
| `resources/views/livewire/clientes/editar.blade.php` | Tab albaranes, sort, enlaces usuarios |
| `resources/views/livewire/usuarios/ver.blade.php` | Sort, `variant="info"` |
| `resources/views/livewire/usuarios/editar.blade.php` | Sort, `variant="info"` |
| `routes/web.php` | Rutas materiales crear/ver/editar |
| `database/seeders/RolesAndPermissionsSeeder.php` | `usuarios.eliminar` para administrador |
