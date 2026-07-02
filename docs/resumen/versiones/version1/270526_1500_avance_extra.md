# Avance — 27/05/2026 (15:00)

Sesión de ajustes sobre Albaranes (estados, firma, datos demo) + endurecimiento del componente de fechas + correcciones en el editor de Proyectos.

---

## 1. Estados de albarán reducidos de 5 a 3

### Decisión

Los albaranes pasan de tener 5 estados (`borrador`, `pendiente_firma`, `firmado`, `facturado`, `archivado`) a **solo 3**: `pendiente_firma` · `firmado` · `facturado`.

Motivos:
- `borrador` quedó **redundante** desde que se creó el modelo `Borrador` (BOR-XXXX) como entidad separada el 27/05/2026 mañana. Un Albarán ya nunca nace como borrador; nace cuando se convierte desde un Borrador o cuando un trabajador lo crea desde el móvil.
- `archivado` no estaba enganchado a ningún flujo de UI (no había botón "archivar") y la política del proyecto para "sacar de circulación" es papelera/desactivar, no archivar.

### Transiciones nuevas

```
pendiente_firma  →  firmado  →  facturado  →  (terminal)
```

`PENDIENTE_FIRMA` es el único estado editable libremente; `FIRMADO` y `FACTURADO` bloquean edición.

### Archivos tocados

| Archivo | Cambio |
|---|---|
| `app/Enums/EstadoAlbaran.php` | Enum reducido a 3 casos. `etiqueta()`, `tono()`, `transicionesPermitidas()`, `esEditable()`, `bloqueaEdicion()` reescritos. |
| `database/migrations/2026_05_14_120000_create_albaranes_tables.php` | Enum del campo `estado` con los 3 valores y default `pendiente_firma`. |
| `database/migrations/2026_05_27_150000_reduce_albaran_estados_a_tres.php` | **Nueva.** Reasigna filas existentes: `borrador → pendiente_firma`, `archivado → facturado`. Re-emite el `ALTER TABLE ... MODIFY COLUMN` en MySQL/MariaDB; SQLite (tests) no soporta MODIFY ENUM y se apoya en el cast del modelo. |
| `database/factories/AlbaranFactory.php` | Default `PENDIENTE_FIRMA`. Eliminado state `archivado()`. |
| `database/seeders/Fase2DemoSeeder.php` | `elegirEstado()` reescrito con pesos por antigüedad (≤14 días → pdte. firma, intermedios → firmados, antiguos → facturados). Resumen del log con 3 contadores. |
| `app/Livewire/Forms/AlbaranForm.php` | Albaranes nuevos nacen en `PENDIENTE_FIRMA`. |
| `app/Livewire/Albaranes/Editar.php` | Quitado el filtro que excluía `BORRADOR` del select de estados. |
| `app/Livewire/Mobile/Albaranes/Firmar.php` | Quitado `ARCHIVADO` de la lista de "ya firmado". |
| `app/Livewire/Borradores/Ver.php` | Al convertir Borrador → Albarán, el nuevo albarán nace en `PENDIENTE_FIRMA`. |
| `app/Livewire/Mobile/Albaranes/Index.php` | Comentario de filtros actualizado. |
| `resources/views/livewire/mobile/albaranes/index.blade.php` | Pill "Borradores" eliminada de los filtros. |
| `resources/views/livewire/mobile/albaranes/ver.blade.php` | Fusionados los dos botones de firma (antes "Iniciar proceso" para borrador + "Completar firma pendiente") en uno solo: "Firmar parte" para `pendiente_firma`. |
| `tests/Unit/EstadoAlbaranTest.php` | Reescrito para 3 estados. |
| `tests/Feature/Mobile/Albaranes/CrearTest.php` | Assert `estado = pendiente_firma`. |
| `tests/Feature/Mobile/Albaranes/VerTest.php` | Test renombrado, ahora usa `PENDIENTE_FIRMA`. |
| `tests/Feature/Mobile/Albaranes/IndexTest.php` | Filtro de prueba apunta a `pendiente_firma`. |

### Verificación

Tras `php artisan migrate:fresh --seed`: 70 albaranes (9 pendientes · 34 firmados · 27 facturados), todos sobre proyectos `activo`.

---

## 2. Datos demo congruentes — Fase2DemoSeeder solo sobre proyectos activos

### Problema

`ProyectoFactory` reparte estados aleatorios (`activo`/`inactivo`/`cerrado`), así que de los 31 proyectos solo ~18 quedaban activos. `Fase2DemoSeeder` no filtraba → se creaban albaranes apuntando a proyectos cerrados o inactivos. Al abrir el editor web de uno de esos albaranes, el selector de proyecto aparecía **vacío** porque solo lista proyectos activos.

### Solución

Doble fix:

- `database/seeders/Fase2DemoSeeder.php` filtra ahora `Proyecto::where('estado', 'activo')` antes de crear albaranes.
- `app/Livewire/Albaranes/Editar.php::proyectosDisponibles()` **incluye el proyecto actual del albarán** aunque esté inactivo/cerrado, para no perder visualmente la selección. Este caso también se da en producción si alguien cierra un proyecto después de generar albaranes.

```php
return Proyecto::query()
    ->where(function ($q) use ($actual): void {
        $q->where('estado', 'activo');
        if ($actual !== null) {
            $q->orWhere('id', $actual);
        }
    })
    ->orderBy('nombre')
    ->get(['id', 'nombre', 'codigo', 'cliente_id', 'estado']);
```

---

## 3. Notificación de firma con feedback visible

### Problema

En el tab Firmas del editor de albarán, al pulsar "Notificar seleccionados" **no pasaba nada visible**:
- La empresa no tenía SMTP configurado (`mail_host = null`).
- `notificarFirmantes()` hacía `$this->addError('firma', ...)` y volvía, pero el blade del editor **no tenía `<x-ui.flash>` ni renderizaba ese error** → sensación de fallo silencioso.

### Solución

- 3 `addError('firma', ...)` → `session()->flash('error', ...)` para hacerlos visibles.
- `enviarTokenFirma()` ahora **atrapa excepciones SMTP** (host inaccesible, credenciales mal, etc.): invalida el token recién creado (sin email no sirve), loggea el detalle en `storage/logs/laravel.log`, vuelca un flash rojo con el mensaje exacto del error y devuelve `false`.
- `notificarFirmantes()` ya no incrementa `$enviados` si el envío falló → no muestra "Notificación enviada" cuando murió.
- Añadido `<x-ui.flash />` justo después del page-header en `resources/views/livewire/albaranes/editar.blade.php`.
- Mensaje de éxito enriquecido: en vez de "Notificación enviada correctamente.", ahora se compone con los destinatarios efectivamente notificados y la fecha de caducidad:

  > ✓ Solicitud de firma enviada a **Empleado (jhormanore005@gmail.com)** y **Responsable (jhormanorre005@gmail.com)**. El enlace de firma caduca el 03/06/2026.

### Archivos

- `app/Livewire/Albaranes/Editar.php` — `notificarFirmantes()` y `enviarTokenFirma()`.
- `resources/views/livewire/albaranes/editar.blade.php` — `<x-ui.flash />` añadido.

---

## 4. URLs de firma respetan `APP_URL`

### Problema

El enlace del email "Firmar ahora" y el campo "Enlace de firma" del panel salían siempre con `localhost/...`, aunque el usuario cambió `APP_URL` a `http://192.168.0.115/CLIENTES/ELECIND/public` para poder abrir el link desde el móvil.

Causa: por defecto `route()` y `url()` de Laravel toman el host del **request actual** (si navegas por `localhost`, generan `localhost`). `APP_URL` solo se respeta en CLI/colas/algunos emails diferidos.

### Solución

En `app/Providers/AppServiceProvider.php::boot()`:

```php
if ($appUrl = config('app.url')) {
    URL::forceRootUrl($appUrl);
    if (str_starts_with($appUrl, 'https://')) {
        URL::forceScheme('https');
    }
}
```

Ahora **todos** los `route()` y `url()` generan URLs con la base de `APP_URL`, independientemente de cómo se acceda al panel. Cuando se despliegue a producción basta con cambiar `APP_URL` en el `.env` del servidor.

### Notas

- `MAIL_MAILER=log` en `.env` solo afecta al mailer por defecto. `notificarFirmantes()` configura un mailer al vuelo (`empresa_smtp`) con las credenciales SMTP de la tabla `empresa`, así que los correos sí salen aunque `MAIL_MAILER=log` siga ahí.
- Los enlaces siguen apuntando a la IP local (192.168.0.115); solo abrirán desde dispositivos en la misma red WiFi/LAN mientras estemos en local.

---

## 5. Componente `date-input` robusto + fecha por defecto al crear

### Problema 1: el campo Fecha "desaparecía"

El componente `<x-ui.date-input>` esconde su `<input>` original (`class="hidden"`) y depende **100 %** de Flatpickr para inyectar un input alternativo visible. Si Flatpickr no se inicializaba (timing, error de carga, conflicto Alpine), el campo se quedaba invisible.

### Problema 2: la fecha por defecto no se mostraba

Aunque `mount()` ponía `$this->form->fecha = now()->format('Y-m-d')`, Flatpickr arrancaba con el calendario vacío. Causa: Livewire **no inyecta el `value=""` en el HTML del SSR cuando la propiedad pertenece a un Form Object** — lo hidrata en cliente vía JS *después* de que Alpine ya haya inicializado Flatpickr. Resultado: en `init()`, `$refs.input.value` era cadena vacía.

### Solución

`resources/views/components/ui/date-input.blade.php`:

1. Nueva prop `value` opcional → se escribe directo en el HTML como `value="..."` (sin depender de Livewire) **y** se pasa como `defaultDate` al config de Flatpickr.
2. El `init()` de Alpine ahora reintenta hasta 20 veces (50 ms cada uno) si `window.flatpickr` aún no está disponible.
3. Si tras 1 s sigue sin estar, **fallback**: el `<input>` deja de estar oculto, se le pone `type="date"` (o `datetime-local` si `enableTime`) y se aplican las clases del campo. Así el navegador muestra su date picker nativo y el campo no desaparece nunca.

Blades actualizados para pasar `:value`:

- `resources/views/livewire/albaranes/editar.blade.php` — `:value="$form->fecha"`
- `resources/views/livewire/proyectos/editar.blade.php` — `:value="$form->fecha_inicio"` y `:value="$form->fecha_fin"`
- `resources/views/livewire/horas/index.blade.php` — `:value="$fechaDesde"` y `:value="$fechaHasta"`
- `resources/views/livewire/mobile/horas/index.blade.php` — idem
- `resources/views/livewire/configuracion/logs.blade.php` — idem

### Fecha por defecto a hoy al crear

Confirmado/añadido en:

- `app/Livewire/Albaranes/Editar.php` (`mount()` web) — ya estaba.
- `app/Livewire/Mobile/Albaranes/Crear.php` — ya estaba.
- `app/Livewire/Borradores/Editar.php` — ya estaba.
- `app/Livewire/Mobile/Albaranes/Personalizado.php` — ya estaba.
- `app/Livewire/Proyectos/Editar.php` — **añadido** en `mount()` y en `deshacer()` para que `fecha_inicio = now()->format('Y-m-d')` al entrar a `/proyectos/crear`. `fecha_fin` queda vacío a propósito (no tiene default sensato).

---

## 6. Editor de Proyectos — filtro de trabajadores por rol Spatie

### Problema

En el tab Trabajadores del editor de proyectos, el select de "Añadir trabajador" listaba todos los `users` con `tipo_usuario = 'interno'` y `activo = true`. **No filtraba por rol Spatie**, así que aparecían usuarios sin rol `trabajador` (admins, superadmins, etc.) que no debían poder asignarse como mano de obra del proyecto.

### Solución

`app/Livewire/Proyectos/Editar.php`:

- `trabajadoresDisponibles()` ahora usa `->role('trabajador')` (de spatie/laravel-permission) en lugar del filtro por `tipo_usuario`.
- `agregarTrabajador()` blindado en backend: si alguien manipula el payload e intenta asignar un usuario sin rol `trabajador`, se devuelve error explícito *"Solo se pueden asignar usuarios con rol trabajador."* — defensa contra DOM tampering.

### Nota

Los trabajadores **ya asignados** al proyecto se siguen mostrando en la tabla aunque hayan perdido el rol después — a propósito, para no perder histórico. Si en el futuro se quiere filtrar también la tabla por rol actual, se cambia en `usuariosProyectoPorRol()`.

---

## 7. Re-render de selects al quitar relaciones N:M en Proyectos

### Problema

Cuando se quitaba un trabajador (o responsable / material / concepto) de un proyecto desde la tabla, la fila desaparecía bien, pero **el usuario no volvía a aparecer en el select de "Añadir trabajador"** hasta recargar la página.

Causa: el `<x-ui.searchable-select>` usa `wire:key="trabajador-select-{{ $trabajadorSelectKey }}"` y ese contador solo se incrementaba en `agregarTrabajador()`, no en `quitarTrabajador()`. El componente conserva su estado Alpine interno (lista de opciones) hasta que la `wire:key` cambia y Livewire lo reemplaza.

### Solución

En `app/Livewire/Proyectos/Editar.php`, los 4 métodos `quitar*` ahora incrementan su contador de select:

- `quitarTrabajador()` → `$this->trabajadorSelectKey++`
- `quitarResponsableProyecto()` → `$this->responsableSelectKey++`
- `quitarMaterialProyecto()` → `$this->materialSelectKey++`
- `quitarConceptoProyecto()` → `$this->conceptoSelectKey++`

Ahora al quitar una fila, el select se re-renderiza y la opción quitada aparece inmediatamente disponible sin recargar.

---

## 8. Resumen de archivos tocados

### Nuevos
- `database/migrations/2026_05_27_150000_reduce_albaran_estados_a_tres.php`

### Modificados (por área)

**Enum + migración base albaranes**
- `app/Enums/EstadoAlbaran.php`
- `database/migrations/2026_05_14_120000_create_albaranes_tables.php`

**Factory + seeder**
- `database/factories/AlbaranFactory.php`
- `database/seeders/Fase2DemoSeeder.php`

**Livewire — Albaranes/Borradores/Mobile**
- `app/Livewire/Forms/AlbaranForm.php`
- `app/Livewire/Albaranes/Editar.php` (estados + firmantes + selector proyecto + flash)
- `app/Livewire/Mobile/Albaranes/Firmar.php`
- `app/Livewire/Mobile/Albaranes/Index.php`
- `app/Livewire/Borradores/Ver.php`

**Livewire — Proyectos**
- `app/Livewire/Proyectos/Editar.php` (fecha_inicio default, filtro por rol, re-render selects)

**Provider — URL forzada**
- `app/Providers/AppServiceProvider.php`

**Componente date-input + blades que lo usan**
- `resources/views/components/ui/date-input.blade.php`
- `resources/views/livewire/albaranes/editar.blade.php`
- `resources/views/livewire/proyectos/editar.blade.php`
- `resources/views/livewire/horas/index.blade.php`
- `resources/views/livewire/mobile/horas/index.blade.php`
- `resources/views/livewire/configuracion/logs.blade.php`

**Blades móvil**
- `resources/views/livewire/mobile/albaranes/index.blade.php`
- `resources/views/livewire/mobile/albaranes/ver.blade.php`

**Tests**
- `tests/Unit/EstadoAlbaranTest.php`
- `tests/Feature/Mobile/Albaranes/CrearTest.php`
- `tests/Feature/Mobile/Albaranes/VerTest.php`
- `tests/Feature/Mobile/Albaranes/IndexTest.php`

---

## 9. Comandos útiles tras estos cambios

```bash
# Aplicar la nueva migración de estados
php artisan migrate

# Regenerar todos los datos demo (3 estados, proyectos activos)
php artisan migrate:fresh --seed

# Tras tocar app.js (no fue el caso hoy, pero como recordatorio)
npm run build

# Tras editar blades en XAMPP
php artisan view:cache

# Limpiar cache de configuración tras cambiar APP_URL
php artisan config:clear
```

---

## 10. Cosas a tener en cuenta para próximas sesiones

### SMTP no configurado todavía
La empresa actualmente tiene `mail_host = null`. Hasta que se configure SMTP en `/configuracion/empresa → Correo`, la notificación de firma mostrará el flash rojo *"Configura el servidor de correo en Ajustes → Correo antes de enviar notificaciones."* pero el `TokenFirma` no se genera. Decisión pendiente del usuario: si en el futuro se quiere desacoplar generación de token y envío de email (para poder copiar el enlace aunque no haya SMTP), hay que refactorizar `enviarTokenFirma()`.

### URL de firma en LAN
Mientras `APP_URL=http://192.168.0.115/CLIENTES/ELECIND/public`, los enlaces solo abren desde dispositivos en la misma red WiFi. Para abrirlos desde fuera hay tres opciones (apuntadas en su momento al usuario): dejarlo así para probar manualmente, usar Mailtrap/Mailpit para inspeccionar correos, o exponer el servidor con un túnel tipo ngrok.

### Refactor pendiente del modal "Asignar materiales" en Familias
Sigue en el backlog desde la sesión anterior (ver memoria `flujo-checkpoint-usuario`). No tocado hoy.

### Fase 2 Iter 4-6 sigue pendiente
La firma canvas + ruta pública `/firmar/{token}` + adjuntos medialibrary siguen en el plan original sin abordar. Hoy se mejoró la infraestructura periférica (flash, URLs, token con error handling) pero el flujo end-to-end de firma desde el móvil del responsable sigue sin probarse hasta configurar SMTP real.
