# Resumen de Sesión — 2026-05-20 · Exportación de Clientes (Fase 5, paso 2)

Fecha: 2026-05-20
Estado: Exportación Excel y PDF **funcional**. Contador total clientes en listado web **funcional**.
Ámbito: Web. Fase 5 (import/export) — continuación directa de la sesión anterior.

---

## 1. Qué se ha construido

### 1.1 Exportar a Excel (`/clientes/exportar/excel`)
Descarga un `.xlsx` con todos los clientes que coincidan con los filtros activos en ese momento.

- Respeta: buscador (`q`), filtro estado, filtro provincia, columna de orden y dirección (asc/desc).
- Cabeceras en español, columna "Activo" como Sí/No, fila 1 en negrita, autosize de columnas.
- Nombre de archivo: `clientes_YYYY-MM-DD.xlsx`.
- Permiso: `clientes.exportar` (ya existía en el seeder).
- Librería: `maatwebsite/excel` (ya instalada desde Fase 0).

### 1.2 Exportar a PDF — Vertical y Horizontal (`/clientes/exportar/pdf/{orientacion}`)
Dos opciones en el menú Acciones: **PDF Vertical** y **PDF Horizontal**.

- Respeta los mismos filtros y orden que el Excel.
- Incluye **todos los campos del modelo Cliente** (12 columnas): Código, Nombre, Nombre Comercial, CIF, Dirección, C.P., Población, Provincia, Teléfono, Email, Activo, Observaciones.
- Cabecera: logo de empresa (cascada `logo_albaran_path → logo_path`; si no hay logo, nombre en texto).
- Colores de la tabla: `color_primario`, `color_secundario` y `color_texto_encabezado` leídos de `Empresa`.
- Footer de página con número de página (`Página X de Y`) y nombre de empresa.
- Nombre de archivo: `clientes_YYYY-MM-DD_vertical.pdf` / `…_horizontal.pdf`.
- Vertical: fuente 6.5pt para caber las 12 columnas en A4 portrait.
- Horizontal: fuente 7.5pt con más espacio en A4 landscape.
- Librería: `mpdf/mpdf v8.3.1` (ver sección de problemas).

### 1.3 Renombrado de botón
"Importar xlsx/csv" → **"Importar Excel"** en el menú Acciones del listado.

### 1.4 Contador total de clientes en la cabecera web
El título de la página muestra **"Clientes (247)"** — número total de clientes activos e inactivos (excluye papelera), independiente de los filtros y la paginación.

- Computed `totalClientes()` en `Clientes\Index`.
- Prop `badge` añadido al componente `ui/page-header` (reutilizable en otros módulos).
- Formato: `(N)` en gris suave alineado con el título, sin fondo ni bordes.

---

## 2. Archivos nuevos / modificados

| Archivo | Estado | Descripción |
|---|---|---|
| `app/Exports/ClientesExport.php` | **Nuevo** | Export Maatwebsite: query con filtros, cabeceras, mapping |
| `app/Http/Controllers/Clientes/ExportarExcelController.php` | **Nuevo** | Controller invocable: lee params URL, devuelve descarga xlsx |
| `app/Http/Controllers/Clientes/ExportarPdfController.php` | **Nuevo** | Controller invocable: query + mPDF directo, acepta `{orientacion}` |
| `resources/views/pdf/clientes/lista.blade.php` | **Nueva** | Template PDF: logo empresa, colores de marca, 12 columnas, footer paginado |
| `routes/web.php` | Modificado | Rutas `clientes.exportar.excel` y `clientes.exportar.pdf` antes del wildcard |
| `resources/views/livewire/clientes/index.blade.php` | Modificado | Botones activos (Excel, PDF V, PDF H), badge total, renombrado importar |
| `resources/views/components/ui/page-header.blade.php` | Modificado | Nuevo prop `badge` opcional, renderizado como `(N)` junto al título |
| `app/Livewire/Clientes/Index.php` | Modificado | Computed `totalClientes()` |
| `bootstrap/providers.php` | Modificado | Limpiado service provider del paquete eliminado |
| `composer.json` / `composer.lock` | Modificado | Eliminado `carlos-meneses/laravel-mpdf`, instalado `mpdf/mpdf ^8.3` |

---

## 3. Problemas encontrados y cómo se resolvieron

### Problema 1 — `Class "Mpdf\Mpdf" not found`
**Causa**: el controller usaba `new \Mpdf\Mpdf()` (namespace moderno de mPDF 8), pero el paquete instalado `carlos-meneses/laravel-mpdf v1.1` arrastra `mpdf/mpdf v6.1.3` — versión antigua sin namespace donde la clase se llama simplemente `mPDF`.

**Intento**: usar el facade del paquete (`Meneses\LaravelMpdf\Facades\LaravelMpdf`).

### Problema 2 — `Target class [mpdf.wrapper] does not exist`
**Causa**: `carlos-meneses/laravel-mpdf` no tiene el bloque `extra.laravel.providers` en su `composer.json` (es previo a la auto-discovery de Laravel 5.5). Laravel 12 no lo registra automáticamente.

**Intento**: registrarlo manualmente en `bootstrap/providers.php`.

### Problema 3 — `Array and string offset access syntax with curly braces is no longer supported`
**Fichero**: `vendor/mpdf/mpdf/mpdf.php:2349`

**Causa raíz de los tres problemas**: `mpdf/mpdf v6.1.3` (2017) usa la sintaxis `{$var}` para acceso a strings/arrays, eliminada en PHP 8.0. El paquete `carlos-meneses/laravel-mpdf` está abandonado y ancla a esta versión incompatible con PHP 8.x.

**Solución definitiva**:
1. `composer remove carlos-meneses/laravel-mpdf` — elimina el paquete wrapper y mPDF 6.
2. `composer require mpdf/mpdf` — instala mPDF 8.3.1, compatible con PHP 8.2, namespace `\Mpdf\Mpdf`.
3. Controller usa `new \Mpdf\Mpdf([...])` directamente, sin wrapper ni facade.
4. Limpiado el service provider de `bootstrap/providers.php`.

---

## 4. Decisiones tomadas y POR QUÉ

| Decisión | Por qué |
|---|---|
| **Exportación respeta filtros activos + orden** | Lo que exportas es exactamente lo que ves en pantalla. Los filtros están en la URL via `#[Url]` → se pasan al link de descarga como query params. |
| **Controller normal en vez de acción Livewire** | Livewire no puede hacer stream de un fichero binario desde una acción. La exportación es una descarga, no una navegación Livewire. |
| **Una ruta `{orientacion}` en vez de dos rutas separadas** | Menos código. El constraint `where('orientacion', 'vertical\|horizontal')` valida el parámetro automáticamente y devuelve 404 para otros valores. |
| **Eliminar `carlos-meneses/laravel-mpdf`, usar mPDF 8 directo** | El wrapper está abandonado, es incompatible con PHP 8.x y no aporta valor real (3 métodos de conveniencia). mPDF 8 se usa con `new \Mpdf\Mpdf()` sin service provider ni facade. |
| **Todos los campos del cliente en el PDF** | El PDF es un informe completo, no un recorte de la tabla web. Decisión explícita del usuario. |
| **CSS en dos bloques `<style>` separados** | El linter CSS de VS Code no entiende `{{ }}` de Blade dentro de `<style>` y lanza falsos positivos. Solución: bloque 1 con CSS puro estático (el IDE lo valida), bloque 2 con `<?= ?>` PHP para los valores dinámicos (colores, tamaños) que el IDE no analiza como CSS. |
| **Contador `(N)` junto al título h2, sin fondo** | Probadas tres posiciones: badge con fondo (descartado: demasiado visual), número a la derecha del todo (descartado: se desconecta del contexto), finalmente inline con el h2 como `(N)` en gris — patrón de Gmail / Linear / Notion. |
| **Prop `badge` en el componente `page-header`** | Reutilizable para Proyectos, Materiales, Usuarios etc. cuando se quiera el mismo contador en otros módulos. |

---

## 5. Estado de calidad

- `php -l` OK en todos los archivos PHP nuevos/modificados.
- Pint passed.
- `route:list` correcto: rutas de exportación antes del wildcard `{cliente}`.
- `view:cache` compila sin errores.
- Pruebas en navegador: Excel ✅ · PDF Vertical ✅ · PDF Horizontal ✅ (confirmados por el usuario).

---

## 6. Pendiente / siguientes pasos

- [ ] **Imprimir lista** de Clientes (único botón "Pronto" restante en Clientes).
- [ ] Replicar exportación Excel + PDF a **Proyectos**, **Materiales**, **Albaranes**, **Conceptos**, **Usuarios**.
- [ ] Contador `(N)` en los otros módulos si se decide extenderlo.
- [ ] Incoherencia de permisos pendiente: `conceptos` y `usuarios` muestran botones import/export pero no tienen permisos `*.exportar/importar` en el seeder.
- [ ] Importación Excel pendiente para Proyectos / Materiales (patrón ya definido en Clientes).
