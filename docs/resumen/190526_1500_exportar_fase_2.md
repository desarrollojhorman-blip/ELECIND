# Resumen Sesión 2026-05-19 15:00 — Importación de Clientes (Fase 5, paso 1)

Fecha: 2026-05-19
Estado general: IMPORTACIÓN DE CLIENTES FUNCIONAL — pendiente prueba navegador del usuario
Ámbito: Web. Primer entregable de Fase 5 (Reportes y exportación / import-export).

> Nota: el nombre del archivo dice "exportar" pero esta sesión cubre **IMPORTACIÓN**.
> La exportación (Excel/PDF/Imprimir) sigue pendiente (botones "Pronto").

---

## 1. Qué se ha construido

**Importación manual de Clientes** desde Excel/CSV con mapeo de columnas.
Pensada como **patrón de referencia** para replicar luego en proyectos, materiales, etc.

### Flujo (3 pasos en una sola pantalla)
1. **Subir**: archivo `.xlsx/.xls/.csv` + opciones (encabezados, fila de inicio) → "Procesar archivo".
2. **Mapear**: tabla de 5 columnas `Columna · Valor 1 · Valor 2 · Valor 3 · Usar como`.
   El select "Usar como" se **auto-sugiere** por el nombre del encabezado.
3. **Importar**: valida TODAS las filas; si todo OK crea los clientes y redirige al listado.

### Entrada en la UI
- En `/clientes`, menú **Acciones → "Importar xlsx/csv"** (enlace `wire:navigate`).
- Si el usuario no tiene permiso: aparece deshabilitado con badge "Sin permiso".

---

## 2. Archivos

| Archivo | Cambio |
|---|---|
| `app/Livewire/Clientes/Importar.php` | **Nuevo** — componente full-page (subir/procesar/mapear/validar/importar) |
| `resources/views/livewire/clientes/importar.blade.php` | **Nueva** — Paso 1 + tabla de mapeo + tabla de errores |
| `routes/web.php` | Ruta `clientes.importar` (`/clientes/importar`) **antes** del wildcard `/clientes/{cliente}`, con `can:clientes.importar` |
| `resources/views/livewire/clientes/index.blade.php` | "Importar xlsx/csv" activo (antes deshabilitado "Pronto") |
| `resources/views/components/ui/actions-menu-item.blade.php` | Mejora reutilizable: soporta `href` (renderiza `<a>`) |
| `app/Support/ClienteFields.php` | Añadido `uniqueFields()` (fuente única de qué campos son únicos) |
| `app/Livewire/Forms/ClienteForm.php` | `rules()` recorre `ClienteFields::uniqueFields()` (ya no hardcodea código) |

Lectura del archivo: **`PhpOffice\PhpSpreadsheet\IOFactory`** (viene con `maatwebsite/excel`, no se usa maatwebsite directamente).

---

## 3. Reglas de la importación (TODAS)

### 3.1 Validación de campos = mismas que "Añadir cliente"
Lee `App\Support\ClienteFields::getValidationRules()` (fuente única de verdad,
compartida con `ClienteForm`). NO se duplican reglas → no se desincronizan.

| Campo | Regla |
|---|---|
| codigo_cliente | required · entero · 1–100000 · único *(solo si se mapea)* |
| nombre | **obligatorio** · texto · máx. 150 |
| nombre_comercial | texto · máx. 150 |
| cif | texto · máx. 20 · **puede repetirse** |
| direccion | texto · máx. 255 |
| codigo_postal | texto · máx. 10 |
| poblacion | texto · máx. 120 |
| provincia | texto · máx. 120 |
| telefono | texto · máx. 30 |
| email | email válido · máx. 150 |
| observaciones | texto · máx. 2000 |
| activo | booleano (ver 3.5) |

### 3.2 Todo o nada (acordado con el usuario)
Al pulsar Importar se validan **todas** las filas primero. Si hay **un solo
error**, NO se guarda nada y se listan **todos** los errores juntos en una
tabla `Fila · Campo · Motivo`. El usuario corrige el archivo y reintenta.

### 3.3 Código de cliente: manual o automático (excluyente)
Lo decide si se asigna la columna *Código de cliente* en el mapeo:
- **Mapeado** → usa el código del archivo (se valida entero/rango/único).
- **NO mapeado** → se autogenera consecutivo (`NumeracionService::siguienteNumeroCliente()`, máximo + 1, +1 por fila).
- Nunca las dos a la vez. Es la única diferencia respecto a "Añadir" (donde el código es obligatorio).

### 3.4 Unicidad centralizada (sin drift)
Qué campos son únicos vive en **`ClienteFields::uniqueFields()`** → hoy `['codigo_cliente']`.
Lo leen los dos: `ClienteForm::rules()` (arma el `Rule::unique`) e `Importar`
(`array_intersect(uniqueFields, mapeados)` → comprobación en lote: contra BD
+ duplicados dentro del propio archivo).
- **CIF**: por decisión de negocio **puede repetirse** → NO está en la lista,
  ni en alta ni en importación (antes la importación lo bloqueaba a mano; corregido).
- Si cambia qué es único, se toca **un solo sitio**.

### 3.5 Campo "activo"
El valor sale de la celda. No distingue mayúsculas/tildes.
- ✅ Activo: `Sí`, `Si`, `S`, `1`, `X`, `Activo`, `Alta`, `True`, `Yes`, o **celda vacía**.
- ❌ Inactivo: `No`, `0`, `Inactivo`, `Baja`, `False`, o **cualquier valor no reconocido**.
- Si NO se mapea la columna → todos se importan como **Activo**.

### 3.6 Encabezados y fila de inicio
- Check **"La primera fila son los títulos"** (por defecto sí): fila 1 = títulos, datos desde la 2.
  Si se destilda: columnas se llaman *Columna A, B, C…* y todas las filas son datos.
- Campo **"Empieza en la fila Nº"** (opcional): salta filas previas (logos/cabeceras).

### 3.7 Auto-sugerencia de mapeo
Solo con encabezados activos. Normaliza el título (minúsculas, sin tildes/símbolos)
y si coincide **exactamente** con un alias conocido, pre-selecciona el campo.
Ejemplos: `Razón social/Empresa/Cliente`→nombre, `NIF/DNI`→cif,
`Ciudad/Localidad/Municipio`→poblacion, `Tlf/Móvil`→telefono, `Correo/Mail`→email,
`CP`→codigo_postal. No repite campo. Es solo sugerencia: el usuario puede cambiar todo.

### 3.8 Límites
- Tamaño máximo de archivo: **15 MB** (constante `MAX_FILE_MB`; texto "máx. 15 MB" junto al label).
- Tope de filas de datos: **5.000** por importación (constante `MAX_FILAS`),
  con aviso amable si se supera (la lectura es síncrona en memoria; más de eso
  requeriría proceso en segundo plano — fuera de alcance v1).

### 3.9 Seguridad
- Ruta y componente protegidos con permiso **`clientes.importar`** (ya existía;
  lo tienen `administrador` y `superadmin`).
- v1 solo **añade** clientes nuevos (no actualiza existentes).

---

## 4. Estado de calidad

- `php -l` OK · **Pint passed** en todos los archivos · `route:list` correcto
  (ruta antes del wildcard) · `view:cache` compila · clases autocargan.
- `ClienteFields` (capa Support) quedó **desacoplado**: sin `use` de clases Livewire.
- **NO verificado**: el recorrido real en navegador (login + xlsx). Falta que el
  usuario lo pruebe y dé el visto bueno.
- Sin red de tests automáticos (suite rota pre-existente por migración
  `codigo_cliente` con `SHOW INDEX` bajo SQLite — incidencia previa, no de esta sesión).

---

## 5. Pendiente

- [ ] Prueba en navegador por el usuario (checkpoint para continuar).
- [ ] **Exportación**: a Excel, a PDF e "Imprimir lista" siguen como botones "Pronto".
- [ ] Replicar el patrón de importación a Proyectos / Materiales / etc.
- [ ] `conceptos` y `usuarios` muestran botones import/export pero **no tienen**
      permisos `*.exportar/importar` en el seeder (incoherencia a resolver).
- [ ] (Futuro) Si se necesita importar > 5.000 filas habitualmente: lectura por
      trozos + job en segundo plano (otra arquitectura).

---

## 6. Decisiones cerradas en esta sesión

| # | Decisión |
|---|---|
| 1 | Importación manual con mapeo de columnas (5 columnas estilo guía del usuario) |
| 2 | Todo o nada: si una fila falla, no se guarda nada; se listan todos los errores |
| 3 | Código: manual si se mapea, autogenerado consecutivo si no (excluyente) |
| 4 | Reglas leídas de `ClienteFields` (nunca duplicar/hardcodear) |
| 5 | Unicidad centralizada en `ClienteFields::uniqueFields()` (CIF puede repetirse) |
| 6 | 15 MB máx. archivo + 5.000 filas máx. por importación |
| 7 | v1 solo añade (no actualiza). Solo Clientes (patrón a replicar) |
