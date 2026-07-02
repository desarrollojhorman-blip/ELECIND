# Análisis del Excel «01_SEGUIMIENTO HORAS 2026»

Cuaderno de campo del análisis. Se va completando hoja por hoja a medida que se exploran. Sirve para repaso rápido sin volver a leer el Excel.

**Archivo**: `docs/excel/tarifas/01_SEGUIMIENTO HORAS 2026 (4).xlsx` · **Tamaño**: 17,67 MB

---

## 0 · Estructura general

14 hojas. Las **6 hojas clave** para diseñar el modelo:

| Hoja | Filas × Cols | Rol |
|---|---|---|
| **TARIFAS** | 236 × 9 | Catálogo de tarifas (cobro al cliente) |
| **TABLA DE EMPLEADOS** | 108 × 8 | Trabajadores y sus 5 tasas (lo que se les paga) |
| **PARAMETRIZACION** | 89 × 22 | Catálogos maestros (clientes, proyectos, meses, festivos) |
| **ENTRADA _DATOS** | 17.974 × 36 | Imputación diaria de horas (los partes) |
| **Mix Horas** | 692 × 36 | Cruce horas/tarifas |
| **INPUT** | 107.653 × 157 | Tablón histórico (raw data) |

Otras hojas (reportes calculados, útiles para ver formato pero no para diseñar modelo): X empleado exide, Computo de horas anuales, informe, informe (2), FABRICACIÓN, Seguimiento horas, Tasa de ausencias, Hoja5 (vacía).

---

## 1 · Hoja TARIFAS

**Rango**: A1:I236 · **236 filas** de catálogo.

### Estructura de columnas

| Col | Contenido |
|---|---|
| A | Etiqueta de grupo ("SOLO EXIDE", "solo vestas") — anotaciones manuales |
| B | Lista de **tipos de hora** (leyenda) — solo en las primeras filas |
| C | (vacía / separador) |
| D | `Nº CLIENTE` |
| E | `CLIENTE` |
| F | `PROYECTO` (código tipo `25PR-1-2`) |
| G | `TIPO PROYECTO` — aquí el cliente lo llama "tipo proyecto" pero en realidad es la **DENOMINACIÓN** del proyecto (MANTENIMIENTO, LIMPIEZA, INGENIERIA, OBRAS, FLAMEADORA, ARMARIOS PC, etc.) |
| H | `TIPO HORA` — **clave compuesta** `{PROYECTO}-{TIPO_HORA}`, ej. `25PR-1-2-Labor` |
| I | `TARIFA` — €/h (o importe flat para los PLUS) |

### Los 13 tipos de hora identificados (leyenda en col B)

**Normales** (van al grupo "Tot Horas Normales"):
- `Labor`
- `Lab Noche`
- `Ex Lab`
- `Ex Lab Noc`
- `Desplaz`
- `Ex Despl`

**Festivos** (van al grupo "Tot Horas Festivas"):
- `Fest`
- `Ex Fes`
- `Fest Noct`
- `Ex Fes Noct`

**Pluses** (importe flat, no por hora):
- `PLUS RETEN` → solo EXIDE
- `PLUS FESTIVO` → solo VESTAS
- `PLUS NOCHE` → solo VESTAS

### Ejemplos de tarifas extraídas

| Cliente | Proyecto | Denominación | Tipo Hora | Tarifa |
|---|---|---|---|---|
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 22,54 €/h |
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 24,77 €/h |
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | **PLUS RETEN** | **75 €** (flat) |
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | PLUS FESTIVO | 0 € |
| EXIDE (2) | 25PR-2-2 | LIMPIEZA | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 20,54 €/h |
| EXIDE (2) | 25PR-2-2 | LIMPIEZA | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 23,85 €/h |
| EXIDE (2) | 25PR-3-2 | INGENIERIA | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 22,54 €/h |
| EXIDE (2) | 25PR-3-2 | INGENIERIA | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 24,77 €/h |
| EXIDE (2) | 25PR-3-2 | INGENIERIA | PLUS RETEN | 75 € (flat) |
| VESTAS (22) | 26PR-4-22 | MANTENIMIENTO | Labor / Ex Lab / Lab Noche / Ex Lab Noc / Fest / … | 21,85 €/h |
| VESTAS (22) | 26PR-5-22 | OBRAS | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 21,85 €/h |
| VESTAS (22) | 26PR-5-22 | OBRAS | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 23,95 €/h |
| LOS DESMONTES (25) | 26PR-8-25 | OBRAS | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 23 €/h |
| LOS DESMONTES (25) | 26PR-8-25 | OBRAS | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 25 €/h |

### Observaciones

1. **EXIDE / VESTAS** suelen tener la misma tarifa para "normal" y "extra" del mismo grupo (no aplican multiplicador).
2. **Las tarifas cambian por proyecto** (mismo cliente, distinto proyecto, distinta tarifa).
3. **Los pluses son importes flat**, no por hora. Valor `0` significa "no aplica para este proyecto".
4. **El "TIPO PROYECTO" del Excel es realmente el nombre/denominación del proyecto** — no encaja con nuestro campo `tipo_proyecto_id`. En nuestra BD se corresponde con `proyectos.nombre`.

### Análisis completo de las 236 filas

**Universo cubierto en TARIFAS**: 17 clientes · 27 proyectos · 14 tipos de hora únicos (13 reales + 1 anomalía `4-22-Labor` que es un error de captura).

#### 17 clientes con tarifas
1 OBRAS EXTRA · 2 EXIDE · 13 AURAY MANAGING · 16 J.GARCIA CARRION LA MANCHA · 22 VESTAS · 25 LOS DESMONTES · 29 LOSAL TM · 39 LACTEOS CUQUERELLA · 48 FELIX SOLIS · 51 PARROS OBRAS · 58 INDUSTRIA DEL MUEBLE MANZARA · 59 QUESERIA ARTESANAL LA SOLANA · 60 LA SOLANA MUEBLES · 70 INDUSTRIAS AGRARIAS CASTELLANAS · 76 IBERCACAO · 79 ALVINESA · 80 LACTEAS GARCIA BAQUERO.

#### 27 proyectos con tarifas (resumen)
Año 2025: 25PR-1-2 a 25PR-22-60 (parcial)
Año 2026: 26PR-1-2 a 26PR-23-16 (parcial)

#### Patrón de tarifas por proyecto

**Patrón A — Una sola tarifa para todo** (VESTAS):
- `25PR-4-22` y `26PR-4-22` (MANTENIMIENTO): TODO a 20,98 / 21,85 €/h. Labor = Lab Noche = Fest = Fest Noct = Ex Lab = Ex Lab Noc = Ex Fes = Ex Fes Noct.

**Patrón B — 2 niveles (normal / festivo)** (LOS DESMONTES, EXIDE 25PR, etc.):
- `26PR-8-25` (LOS DESMONTES OBRAS): 23 €/h en Labor+Ex Lab+Lab Noche+Ex Lab Noc; 25 €/h en Fest+Ex Fes+Fest Noct+Ex Fes Noct.
- `25PR-1-2` (EXIDE MANTENIMIENTO): 22,54 €/h normales / 24,77 €/h festivos.
- `26PR-9-39` (LACTEOS CUQUERELLA): 19,85 / 22,05.

**Patrón C — 3 niveles (día / noche / festivo)** (FELIX SOLIS):
- `25PR-12-48` (MANTENIMIENTO): Labor+Ex Lab = 24; Lab Noche+Ex Lab Noc = 25; Fest+Ex Fes+Fest Noct+Ex Fes Noct = 27.

**Patrón D — 4 niveles (día / noche / festivo / festivo noche)** (ALVINESA):
- `25PR-16-79` (OBRAS): Labor+Ex Lab = 25; Lab Noche+Ex Lab Noc = 27; Fest+Ex Fes = 30; Fest Noct+Ex Fes Noct = 32.

**Patrón E — 2 niveles asimétrico (Labor solo / resto a un precio)** (LACTEAS GARCIA BAQUERO, J.GARCIA CARRION):
- `25PR-14-80` (OBRAS): Labor+Ex Lab = 27; TODO LO DEMÁS = 32.
- `26PR-23-16` (OBRAS): Labor+Ex Lab = 25; resto = 27.

### 🚨 HALLAZGO CRÍTICO sobre extras

**Las "extras" SIEMPRE se cobran al mismo precio que la normal del mismo tipo de hora.**

En las 27 tarifas analizadas, **sin excepción**:
- `Labor` y `Ex Lab` → MISMO importe
- `Lab Noche` y `Ex Lab Noc` → MISMO importe
- `Fest` y `Ex Fes` → MISMO importe
- `Fest Noct` y `Ex Fes Noct` → MISMO importe

> **Consecuencia para el modelo**: el cliente solo necesita **4 tarifas distintas por proyecto** (Labor, Lab Noche, Fest, Fest Noct), no las 8. La distinción "extra/no extra" no aplica al cobro al cliente — solo al coste del trabajador.

> **Aclaración pregunta §6.3 anterior**: lo que parecía "FELIX SOLIS cobra Ex Fes a 27 mientras Labor a 24" fue una interpretación equivocada. Fest también está a 27. Ex Fes y Fest van iguales.

### 🚨 HALLAZGO CRÍTICO sobre cambio de año

Las tarifas **suben con el año cambiando de proyecto**:

| Cliente | Proyecto 2025 | Tarifa 2025 | Proyecto 2026 | Tarifa 2026 |
|---|---|---|---|---|
| EXIDE | 25PR-1-2 (MANT) | 22,54 / 24,77 | 26PR-1-2 (MANT) | 23,45 / 25,76 |
| EXIDE | 25PR-2-2 (LIMP) | 20,54 / 23,85 | 26PR-2-2 (LIMP) | 21,37 / 24,81 |
| EXIDE | 25PR-3-2 (INGEN) | 22,54 / 24,77 | 26PR-3-2 (INGEN) | 23,45 / 25,76 |
| VESTAS | 25PR-4-22 (MANT) | 20,98 todos | 26PR-4-22 (MANT) | 21,85 todos |
| VESTAS | 25PR-5-22 (OBR) | 21 / 23 | 26PR-5-22 (OBR) | 21,85 / 23,95 |
| LACTEAS GARCIA BAQUERO | 25PR-14-80 (OBR) | 27 / 32 | 26PR-14-80 (OBR) | 27,73 / 32,85 |
| EXIDE PLUS RETEN | 25PR-1-2 | **75 €** | 26PR-1-2 | **100 €** |

> **Consecuencia para el modelo**: NO HACE FALTA `vigente_desde/hasta` en la tabla de tarifas. El cliente sigue el patrón **un proyecto nuevo por año**, y la tarifa cuelga del proyecto. Al renovar contrato, se crea proyecto nuevo con tarifa nueva.

### Pluses observados (3 tipos)

| Plus | Importes vistos | Clientes |
|---|---|---|
| **PLUS RETEN** | 75 € (EXIDE 2025), 100 € (EXIDE 2026), 30 € (FELIX SOLIS), 0 € (resto) | EXIDE, FELIX SOLIS |
| **PLUS FESTIVO** | 0 € en todos los proyectos vistos | VESTAS (definido, no usado) |
| **PLUS NOCHE** | 0 € en todos los proyectos vistos | VESTAS, FELIX SOLIS (definido, no usado) |

> **Consecuencia**: `0 €` = "concepto no se factura para este proyecto". Es la forma de "no usar". El catálogo tiene 3 pluses pero solo `PLUS RETEN` se usa de verdad (EXIDE y FELIX SOLIS).

### `Desplaz` / `Ex Despl`

Solo aparecen en `26PR-11-76` (IBERCACAO OBRAS) con tarifa 0. **Hipótesis**: están en el catálogo de tipos de hora pero **no se cobran**. Pendiente confirmar en `ENTRADA _DATOS` si se imputan horas de desplazamiento o se descartan.

### Anomalía en los datos

En la fila ~107 hay `25PR-5-22-4-22-Labor` (concatenación errónea de claves). Probablemente fórmula mal copiada en una celda. Ignorable.

---

## 2 · Hoja TABLA DE EMPLEADOS

**Rango**: A1:H108 · **91 empleados** con datos (filas 3-93, resto vacío).

### Estructura

| Col | Contenido |
|---|---|
| B | `Nº OPERARIO` |
| C | `NOMBRE` |
| D | `TASA (HORA)` — laboral diurna base |
| E | `TASA EXTRA` |
| F | `TASA NOCT` |
| G | `TASA FEST` |
| H | `TASA FEST NOCT` |

### Patrones de tasas detectados (las 5 tasas se agrupan, no son únicas)

**Patrón A** (extra=11, noct=11, fest=13, fest_noct=13):
- Tasa hora variable (17.7 a 22.0)
- `TASA EXTRA = TASA NOCT`, `TASA FEST = TASA FEST NOCT`
- Empleados: la mayoría

**Patrón B** (extra=12, noct=12, fest=14, fest_noct=14):
- Plus uniforme superior
- Empleados: 13, 23, 24, 62, 102, 342

**Patrón C** (extra=11.25, noct=13.333, fest=13.333, fest_noct=15):
- `TASA NOCT = TASA FEST` (raro)
- Empleados: 49, 77, 88, 97, 112, 115, 121, 129, 131, 132, 135, 137, 146, 158, 190, 193, 205, 277, 284, 286, 292, 295, 297, 298, 300, 308, 332, 351, 353, 356, 358, 365, 366, 372-376, 378, 381

**Excepciones**:
- Empleado 2 JIMENEZ BELLÓN: tasa hora = 35 (el más caro), patrón A
- Empleado 139 GAVAN MIHAI: tasa hora = 18 (el más barato), patrón A
- Empleado 277 SANCHEZ-BERMEJO: tasa hora = 16,80 (el más barato del C)

### 🚨 Mapeo de tipos de hora a tasas (8 tipos → 5 tasas)

Cruzando los datos del reporte Felix Solis:
- Labor: 162,50h × 18,29 €/h coste → coincide con TASA HORA (~18-20 en Patrón C)
- Ex Lab: 2h × 11 €/h coste → coincide con `TASA EXTRA` (11,25 en Patrón C, redondeado)
- Ex Fes: 9h × 13 €/h coste → coincide con `TASA FEST` (13,333 en Patrón C)

Esto confirma el mapeo:

| Tipo Hora | Tasa del trabajador |
|---|---|
| `Labor` | TASA HORA |
| `Lab Noche` | TASA NOCT |
| `Fest` | TASA FEST |
| `Fest Noct` | TASA FEST NOCT |
| `Ex Lab` | TASA EXTRA |
| `Ex Lab Noc` | ¿TASA NOCT? ¿TASA EXTRA? — pendiente confirmar |
| `Ex Fes` | TASA FEST (mismo que normal festivo) |
| `Ex Fes Noct` | TASA FEST NOCT (mismo que normal festivo noche) |
| `Desplaz` | ¿? — pendiente |
| `Ex Despl` | ¿? — pendiente |
| `PLUS *` | NO se pagan al trabajador (no aparecen en su tabla) |

### 🚨 Observación crítica sobre el "coste" del reporte

En los reportes de rentabilidad, el "Coste/hora" es **muy bajo** comparado con "Precio/hora" (a veces NEGATIVO el margen). Para Felix Solis Ex Lab: cobra 24 €/h, paga 11 €/h → margen 13 €/h (54%). Para Ex Fes: cobra 27 €/h, paga 13 €/h → margen 14 €/h (51%).

**El coste hora bajo sugiere que las tasas son "plus marginales", NO el coste total del empleado.** El sueldo fijo del trabajador no entra en el cálculo de rentabilidad — solo los plus VAR que se le pagan por trabajar fuera de horario normal. Pendiente confirmar esta hipótesis con el cliente o leyendo más reportes.

### Cifras útiles
- **Empleado más caro**: JIMENEZ BELLÓN (35 €/h base, patrón A)
- **Empleado más barato**: SANCHEZ-BERMEJO DIAZ-SALAZAR (16,80 €/h, patrón C)
- **Mediana**: ~19,5 €/h en patrón C, ~20-21 €/h en patrón A
- Hay **gaps en la numeración** (operarios 1, 3, 4, 5, 6, 7, 8, 9, etc. no están) → bajas históricas. Esto encaja con nuestro modelo `users.numero_empleado` libre/HR.

---

## 3 · Hoja PARAMETRIZACION

### Estructura general (primer vistazo a 30 filas)

22 columnas que contienen **catálogos maestros** separados horizontalmente:

| Col | Contenido |
|---|---|
| B-C | Lista maestra de **clientes** (Nº + nombre) |
| E | `CODIGO DE PROYECTO` (`25PR-1-2`, `25PR-2-2`, etc.) |
| F | `Nº CLIENTE` del proyecto |
| G | `CLIENTE` del proyecto |
| H | `ORDEN` secuencial (1, 2, 3…) |
| I | `DENOMINACION PROYECTO` (MANTENIMIENTO, OBRAS, FLAMEADORA, ARMARIOS PC, etc.) |
| J | `AÑO` |
| L | `MES` (Enero…Diciembre) |
| M | `nº Mes` (1-12) |
| O | `DIA SEM` (1-7) |
| P | `DIA` (lunes…domingo) |
| Q | `LABORABLE` (SI/NO) |
| S | `AÑOS` (2024-2037, listado) |
| U-V | `Fecha` + `Festivos` (nombre del festivo) |

### Catálogos visibles en las primeras 30 filas

**Clientes** (col B-C, lista parcial):
- 1 OBRAS EXTRA
- 2 EXIDE TECHNOLOGIES S.L.U
- 3 TALLER
- 5 TALLERES PEREZ VILLENA
- 6 AMS COMPONENTES Y SUMINISTROS S.L
- 10 MIGUEL VELACORACHO MORENO
- 13 AURAY MANAGING S.L.U
- 16 J.GARCIA CARRION LA MANCHA S.A
- 22 VESTAS MANUFACTURING SPAIN S.L.U
- 25 LOS DESMONTES HOSTELERIA S.L
- 29 LOSAL TM SC
- 36 ECOLUNA
- 39 LACTEOS CUQUERELLA S.L
- 42 ENERLIN INGENIEROS S.L
- 44 MARE INGENIERIA
- 45 S.E.CARBUROS METALICOS S.A
- 48 FELIX SOLIS S.L
- 51 PARROS OBRAS SL
- 58 INDUSTRIA DEL MUEBLE MANZARA SL
- 59 QUESERIA ARTESANAL LA SOLANA S.L
- 60 LA SOLANA MUEBLES SLL
- 66 JIMEGAR S.C
- 67 GARCIA CARRION 1890 SL
- 70 INDUSTRIAS AGRARIAS CASTELLANAS SA
- 71 CUNOVESA IBERICA S.L
- 76 IBERCACAO SA
- (continúa…)

**Proyectos** (col E-I, lista parcial):

| Código | Cliente | Denominación |
|---|---|---|
| 25PR-1-2 | EXIDE | MANTENIMIENTO |
| 25PR-2-2 | EXIDE | LIMPIEZA |
| 25PR-3-2 | EXIDE | INGENIERIA |
| 25PR-4-22 | VESTAS | MANTENIMIENTO |
| 25PR-5-22 | VESTAS | OBRAS |
| 25PR-6-13 | AURAY MANAGING | MANTENIMIENTO |
| 25PR-7-13 | AURAY MANAGING | OBRAS |
| 25PR-8-25 | LOS DESMONTES | OBRAS |
| 25PR-9-39 | LACTEOS CUQUERELLA | MANTENIMIENTO |
| 25PR-10-76 | IBERCACAO | MANTENIMIENTO |
| 25PR-11-76 | IBERCACAO | OBRAS |
| 25PR-12-48 | FELIX SOLIS | MANTENIMIENTO |
| 25PR-13-48 | FELIX SOLIS | OBRAS |
| 25PR-14-80 | LACTEAS GARCIA BAQUERO | OBRAS |
| 25PR-15-1 | OBRAS EXTRA | OBRAS |
| 25PR-16-79 | ALVINESA NATURAL INGREDIENTS | OBRAS |
| 25PR-17-29 | LOSAL TM SC | OBRAS |
| 25PR-18-3 | TALLER | **COLOCAR ALMACEN** |
| 25PR-19-58 | INDUSTRIA DEL MUEBLE MANZARA | OBRAS |
| 25PR-20-70 | INDUSTRIAS AGRARIAS CASTELLANAS | OBRAS |
| 25PR-21-59 | QUESERIA ARTESANAL LA SOLANA | OBRAS |
| 25PR-22-60 | LA SOLANA MUEBLES | OBRAS |
| 25PR-23-22 | VESTAS | **ARMARIOS PC** |
| 25PR-24-2 | EXIDE | **FLAMEADORA** |
| 25PR-25-76 | IBERCACAO | **CUADRO CONCHA 4** |
| 25PR-26-2 | EXIDE | CUADRO DISTRIBUCIÓN TRANSFORMADOR 3 |

**Festivos** (col U-V): AÑO NUEVO, REYES, Jueves Santo, Viernes Santo, Lunes de Pascua, DIA DEL TRABAJADOR, Corpus, DIA DE CASTILLA LM, ASUNCION DE LA VIRGEN, HISPANIDAD, TODOS LOS SANTOS, CONSTITUCION, inmaculada, NAVIDAD, LOCAL LA SOLANA, LOCAL MANZANARES (varios años).

### Observaciones

1. **El "TIPO PROYECTO" del Excel = `DENOMINACION` del proyecto**. Puede ser una categoría general (MANTENIMIENTO, OBRAS, LIMPIEZA, INGENIERIA) o un nombre específico de obra (FLAMEADORA, CUADRO CONCHA 4, ARMARIOS PC, CUADRO DISTRIBUCIÓN TRANSFORMADOR 3, COLOCAR ALMACEN). En nuestra BD esto encaja con `proyectos.nombre`, no con `proyectos.tipo_proyecto_id`.
2. **Código de proyecto** sigue patrón `{AA}PR-{ORDEN}-{Nº_CLIENTE}` (ej. `25PR-1-2` = año 25, orden 1, cliente 2). Coincide en estructura con nuestro `proyectos.codigo`.
3. **El cliente lleva calendario propio** con festivos nacionales + locales (La Solana, Manzanares).

### Catálogo completo (filas 5-89)

**37 clientes** en la lista maestra (cols B-C). Pero solo **17 tienen tarifas en la hoja TARIFAS**. Los otros 20 son clientes sin tarifas activas — probablemente históricos sin operación actual.

**~85 proyectos** totales:
- **Año 2025**: 57 proyectos (`25PR-1-2` a `25PR-57-2`)
- **Año 2026**: ~28 proyectos vistos hasta `26PR-28-93` (continúa)

### 🚨 Hallazgo: solo 27 de los ~85 proyectos tienen tarifa

**58 proyectos NO tienen entrada en TARIFAS** → confirma que la regla es:
- **Proyecto con tarifa** → se factura al cliente.
- **Proyecto sin tarifa** → no se factura (interno, garantía, formación, "OBRAS EXTRA", etc.).

Ejemplos de proyectos sin tarifa (en TALLER, CIMY, EXIDE interno, etc.):
- `25PR-18-3` TALLER · COLOCAR ALMACEN
- `25PR-24-2` EXIDE · FLAMEADORA
- `25PR-47-3` TALLER · FORMACION
- `25PR-53-2` EXIDE · ELEVADORES Y TRANSPORTE L10 (recuerda: 151h, 0€ facturación, 2.065€ coste — encaja)
- `26PR-26-93` LEUK · HUERTO SOLAR

### Tipos de denominación de proyecto observados

**Categorías genéricas** (las "típicas" como `tipo_proyecto_id` en nuestra BD):
- MANTENIMIENTO, LIMPIEZA, INGENIERIA, OBRAS, FORMACION

**Nombres específicos** (cada uno único, es un proyecto concreto):
- COLOCAR ALMACEN, ARMARIOS PC, FLAMEADORA, CUADRO CONCHA 4, CUADRO DISTRIBUCIÓN TRANSFORMADOR 3, ARREGLOS MESAS DE CARGA, MODIFICACIÓN DE PANTALLAS, BASCULA AZ, CUADROS REPSOL 2025, CUADRO CLIMATIZACION, OLEO EL PASO, PANTALLA, ELEVADOR TRENES, CUADRO ALUMBRADO TERMINACION, CUADRO BOMBEO, ENCHUFES EVAPORATIVOS, CUADRO SCHNEIDER, CAMINOS MONTAJE, TOLVA DE RECORTES, CASETA LIMPIEZA CASCOS, CUADRO DISTRIBUCION, LATIGUILLOS, CUADRO SERVEO (ALMODOVAR), CUADRO ASPIRACIÓN, CABLEADO, BASCULA DESPERDICIOS L10, ELEVADORES Y TRANSPORTE L10, CUADRO CUARENTENA, CUADRO DISTRIBUCION L10 MONTAJE, CUADRO L1 EMPASTERÍA, PARQUE ALTA TENSIÓN, TURBINA ASPIRACIÓN L10, CUADRO ENERGÍA L10, HUERTO SOLAR, CUADRO DISTRIBUCIÓN NAVE ENSACADORA, SUSTITUCION TRAFOS.

> **Mapeo en nuestra BD**: la denominación específica encaja con `proyectos.nombre`. La categoría genérica (MANTENIMIENTO, OBRAS, etc.) **podría** mapearse a `tipo_proyecto_id` si la queremos categorizar — pero el cliente no tiene catálogo separado; lo escribe libre. Sugerencia: dejar `tipo_proyecto_id` opcional y derivar la categoría del nombre si interesa para reportes.

### Festivos catalogados (col V)

AÑO NUEVO, REYES, Jueves Santo, Viernes Santo, Lunes de Pascua, DIA DEL TRABAJADOR, Corpus, DIA DE CASTILLA LM, ASUNCION DE LA VIRGEN, HISPANIDAD, TODOS LOS SANTOS, CONSTITUCION, inmaculada, NAVIDAD, LOCAL LA SOLANA, LOCAL (Manzanares).

Tienen códigos seriales de Excel (46023 = 2026-01-01 aprox.). Útil para validar si un día es festivo en la imputación. **En nuestra BD no hay tabla de festivos aún** — sería útil añadirla para validar/auto-calcular `tipo_hora=festivo`.

---

## 4 · Hoja ENTRADA _DATOS

**Rango**: A1:AJ17974 · **17.974 filas** de imputación. **36 columnas**.

### Cabecera (fila 7, las 36 columnas)

| Bloque | Columnas |
|---|---|
| Calendario | `AÑO` · `MES` · `Nº MES` · `Mes Exide` · `FECHA` (serial Excel) · `Dia Sem` (1-7) |
| Día | `LABORABLE` (SI/NO) · `FESTIVO` (nombre del festivo nacional/local) · `TIPO DIA` (SI/NO, derivado) |
| Trabajador | `Nº OPERARIO` · `OPERARIO` |
| Cliente/Proyecto | `Nº CL` · `CLIENTE` · `PROYECTO` (código) · `TIPO PROYECTO` (denominación) |
| **TIPO _ HORAS Normales** | `Labor` · `Lab Noche` · `Ex Lab` · `Ex Lab Noc` · `Desplaz` · `Ex Despl` |
| Suma | `Tot Horas Normales` |
| **TIPO _ HORAS Festivas** | `Fest` · `Ex Fes` · `Fest Noct` · `Ex Fes Noct` |
| Suma | `Tot Horas Festivas` |
| Suma total | `Tot Horas Proyecto` |
| **No productivas** | `LICENCIAS` · `Ausencia` · `BAJAS` · `VACACIONES` |
| **Pluses** | `PLUS FESTIVO` · `PLUS NOCHE` · `PLUS RETEN` |

### Patrón observado (primeras 20 filas)

Trabajador 105 (GALLASTEGUI), proyecto 26PR-1-2 (EXIDE MANTENIMIENTO):

| Fecha serial | Día | Festivo | Tipo día | Labor | Ex Lab | Fest | Ex Fes | Vacac | Plus | Tot |
|---|---|---|---|---|---|---|---|---|---|---|
| 46017 | sáb | — | SI | 8 | | | | | | 8 |
| 46018 | dom | — | NO | | | | | | | 0 |
| 46019 | lun | — | NO | | | | | | | 0 |
| 46020 | mar | — | SI | 8 | | | | | | 8 |
| 46021 | mié | — | SI | 8 | | | | | | 8 |
| 46022 | jue | — | SI | | | | | **1** | | 0 |
| 46023 | vie | AÑO NUEVO | NO | | | | | | | 0 |
| 46024 | sáb | — | SI | 8 | **1** | | | | | 9 |
| 46028 | mié | REYES | NO | | | | **2** | | | 2 |

### 🚨 Conclusiones clave de la imputación

1. **Granularidad = una fila por (trabajador × proyecto × DÍA)**, con todos los días del calendario aunque sea fin de semana (línea con todo en 0).
   - Esto explica las 17.974 filas: ~50 trabajadores × ~30 proyectos × ~12 días/mes × 1 año.
   - Es **MUY** distinto a nuestro `albaran_lineas_personal` actual donde las líneas se agrupan por albarán.

2. **Misma fila puede tener varios tipos de hora**: el día 46024 tiene `Labor=8` + `Ex Lab=1` = 9 horas totales.
   - Nuestro modelo actual `horas + horas_extra` se queda corto: aquí hay 6 columnas de normales + 4 de festivas = **10 posibles tipos de hora** por fila.

3. **`TIPO DIA` (SI/NO)** = derivado: `SI` si laborable y no es festivo; `NO` si fin de semana o festivo.

4. **`FESTIVO`** lleva el nombre del festivo nacional/local que aplica ese día (vacío si no aplica).

5. **Vacaciones y categorías no productivas se imputan** en columnas dedicadas (`LICENCIAS`, `Ausencia`, `BAJAS`, `VACACIONES`). Valor = días o horas (pendiente confirmar — fila 46022 tiene `VACACIONES=1`).

6. **Pluses se imputan en columnas independientes** (`PLUS FESTIVO`, `PLUS NOCHE`, `PLUS RETEN`). Valor = unidades del plus (1 = un plus que aplicar). El importe sale por multiplicar con la tarifa del plus en la hoja TARIFAS.

### 🚨 Hallazgo crítico para el modelo

**La estructura actual `albaran_lineas_personal.horas + horas_extra` NO encaja.**

El cliente imputa de forma muy granular: cada día, cada trabajador, cada proyecto, cada tipo de hora. Y un mismo día puede mezclar Labor + Ex Lab + Fest + plus + vacaciones.

**Opciones para el modelo:**
- **A · Una columna por tipo de hora** (10 columnas: horas_labor, horas_lab_noche, horas_ex_lab, horas_ex_lab_noc, horas_desplaz, horas_ex_despl, horas_fest, horas_ex_fes, horas_fest_noct, horas_ex_fes_noct) + 4 de no-productivas + 3 de pluses = ~17 columnas en `albaran_lineas_personal`.
  - Ventaja: una sola fila por trabajador-proyecto-día.
  - Desventaja: muy ancho, poco normalizado, difícil de extender si añaden nuevos tipos.
- **B · Una línea por (trabajador, proyecto, día, tipo_hora)** con `cantidad` única.
  - Ventaja: relacional, fácil extender (nuevo tipo de hora = nueva fila, no nueva columna).
  - Ventaja: agregaciones por SQL fáciles (SUM(cantidad) GROUP BY tipo_hora).
  - Desventaja: muchas filas (un día con 3 tipos = 3 filas).
- **C · Híbrido**: una línea diaria con horas_normales/horas_extras/horas_festivas + JSON column con desglose por tipo.
  - Ventaja: lo mejor de ambos mundos.
  - Desventaja: queries más complejas, JSON no es relacional.

Mi recomendación inicial: **opción B** (una fila por tipo de hora). Es lo más limpio, escala con nuevos tipos sin migrar schema, y los reportes que ya tenemos en SQL (sum por tipo) salen directos.

Pero **decisión a confirmar con el cliente** cómo quieren introducir los datos: ¿una pantalla con 10 inputs de horas por línea (modelo A) o ir añadiendo líneas por tipo de hora (modelo B)?

---

## 5 · Hoja Mix Horas

**Rango**: A1:F30 (la hoja declara hasta AJ448 pero solo hay datos hasta la fila 30, columnas A-F).

Es una **tabla dinámica de Excel** (pivot) que consolida horas por tipo de proyecto. **No es origen de datos**, es reporting derivado.

### Estructura

Cabecera en fila 13: `TIPO PROYECTO | Horas | Peso. | Coste/hora € Medio | Precio/Hora | % Margen`.

### Datos (filas 14-29)

| TIPO PROYECTO | Horas | Peso | Coste €/h | Precio €/h | Margen |
|---|---:|---:|---:|---:|---:|
| MANTENIMIENTO | 38.401 | 64,6% | 19,43 | 22,71 | 14,4% |
| INGENIERIA | 10.706 | 18,0% | 18,64 | 23,59 | 21,0% |
| OBRAS | 8.376 | 14,1% | 17,73 | 22,74 | 22,1% |
| LIMPIEZA | 1.220 | 2,1% | 17,13 | 22,83 | 25,0% |
| (13 tipos "menores": ELEVADORES, CUADROS, BASCULA, TURBINA, FORMACION, HUERTO…) | ~750 | ~1,2% | varios | 0 | — |
| **Total general** | **59.453** | 100% | 18,96 | 22,59 | **16,1%** |

### Notas

- Los 13 tipos "menores" tienen **Precio/Hora = 0** porque son proyectos sin tarifa (proyectos internos, formación, garantías…). Coincide con el hallazgo de PARAMETRIZACION: 58 de ~85 proyectos no tienen tarifa.
- En nuestra BD esto se replica con un reporte SQL: `SELECT tipo_proyecto, SUM(horas), AVG(tasa_empleado), AVG(tarifa_proyecto), margen FROM lineas_personal JOIN proyectos GROUP BY tipo_proyecto`.
- **Acción para v2**: dashboard "Mix de horas" que regenere esta tabla a partir de la BD. No necesita migración, es solo query + vista.

---

## 6 · Hoja INPUT

**Rango declarado**: A1:FA107653 (157 cols). **Solo las primeras 26 columnas tienen datos** — las 131 restantes son padding del modelo Excel.

### 🚨 INPUT es la versión LONG (melted) de ENTRADA_DATOS

Donde ENTRADA_DATOS tiene 10 columnas de tipos de hora + 4 de no-productivas + 3 de pluses por fila, **INPUT explosiona cada una de esas columnas en una FILA**. Una fila por **(operario, día, proyecto, Atributo)**.

Por eso pasa de 17.974 filas (wide) a ~108k filas (long). Aprox 16 atributos por trabajador-día-proyecto.

### Las 26 cabeceras con datos

| Col | Cabecera | Significado |
|---|---|---|
| A | AÑO | |
| B | MES | |
| C | Nº MES | |
| D | Mes Exide | Mes del año fiscal de Exide |
| E | FECHA | Serial Excel |
| F | TIPO DIA | SI/NO laborable |
| G | Nº OPERARIO | |
| H | OPERARIO | |
| I | Nº CL | |
| J | CLIENTE | |
| K | PROYECTO | |
| L | TIPO PROYECTO | Denominación |
| **M** | **Atributo** | **Tipo de hora o plus** (Labor, Ex Lab, …, PLUS RETEN, LICENCIAS, …) |
| **N** | **Horas** | Cantidad si Atributo es tipo de hora |
| **O** | **Pluses** | Cantidad si Atributo es plus (1 = aplica) |
| P | Clave | `{PROYECTO}-{Atributo}` (key VLOOKUP) |
| Q | Tabla_Tarifas.TARIFA | €/h del cliente o flat del plus |
| **R** | **FACTURACION** | `Horas × TARIFA` |
| **S** | **Fact Plus** | `Pluses × TARIFA` |
| T-X | TASAS_OPERARIOS.\* | 5 tasas del operario por VLOOKUP |
| **Y** | **Coste** | `Horas × tasa_aplicable_al_atributo` |
| **Z** | **Coste Plus** | Coste fijo del plus al trabajador (60 € PLUS RETEN, 16 € PLUS FESTIVO) |

### Atributos únicos encontrados (16, muestra de 5.000 filas)

| Atributo | Filas (muestra) | Notas |
|---|---:|---|
| Labor | 384 | Tipo hora normal |
| Lab Noche | 392 | Tipo hora normal |
| Ex Lab | 376 | Tipo hora normal |
| Ex Lab Noc | 421 | Tipo hora normal |
| Fest | 417 | Tipo hora festivo |
| Ex Fes | 382 | Tipo hora festivo |
| Fest Noct | 402 | Tipo hora festivo |
| Ex Fes Noct | 387 | Tipo hora festivo |
| Ex Despl | 5 | Desplazamiento (apenas se usa) |
| LICENCIAS | 395 | No productiva |
| Ausencia | 395 | No productiva |
| BAJAS | 1 | No productiva |
| VACACIONES | 3 | No productiva |
| PLUS NOCHE | 388 | Plus flat |
| PLUS FESTIVO | 390 | Plus flat |
| PLUS RETEN | 389 | Plus flat |

> `Desplaz` (sin "Ex") no aparece en la muestra → probablemente catalogado pero sin uso.

### 🚨 HALLAZGOS

1. **El modelo del cliente ES la opción B** (long format). El cliente ya piensa en (operario × día × proyecto × tipo_hora) como entidad atómica. ENTRADA_DATOS es solo una vista "wide" para captura humana.

2. **Los pluses SÍ tienen coste al trabajador** (contradice la hipótesis anterior):
   - PLUS RETEN: factura 100 € al cliente · cuesta 60 € al trabajador · margen 40 €.
   - PLUS FESTIVO: factura 0 € al cliente · cuesta 16 € al trabajador · **margen -16 € (pérdida)**.
   - Esto significa que el catálogo de pluses tiene DOS importes: `tarifa_cliente` (lo que se cobra) y `coste_trabajador` (lo que se paga).
   - El `coste_trabajador` parece **constante** por tipo de plus (no depende del trabajador): 60 € fijo PLUS RETEN, 16 € fijo PLUS FESTIVO.

3. **Granularidad de pluses RESUELTA** (responde Q1 del v2/nuevo.md):
   - El plus se imputa **por (operario × día × proyecto)** con `Pluses = N` (siendo N veces que aplica).
   - Es independiente de las horas trabajadas — puede haber 0 horas y 1 plus, o 8 horas y 1 plus.
   - **Lo paga el cliente Y se le paga al trabajador**, con importes distintos.

4. **VACACIONES, BAJAS, LICENCIAS, Ausencia son atributos como cualquier otro** (no son un módulo aparte). Se imputan en la columna `Horas` con `Atributo = "VACACIONES"`. Tarifa = 0 (no se factura), Coste = 0 (en la muestra). Pendiente confirmar si la baja también es 0€ coste al trabajador (parece que sí, son horas "no productivas" que no se imputan ni a cliente ni a trabajador a efectos de plus).

5. **Tarifa = 0 → no se factura** confirmado en las muestras. `tarifa = 0` es la forma de marcar "no aplica" sin borrar la entrada.

### Totales de la muestra (5.000 filas de las 107.653)
- Facturación: **64.701,63 €**
- Coste: **54.670,92 €**
- Margen muestra: 10.030,71 € (≈15,5%)

Si extrapolas al total (×21,5), salen ~1,39M facturación / 1,18M coste / 210k margen anual. Coincide con el orden de magnitud de Mix Horas (59k horas × 22,6 €/h ≈ 1,34M).

---

## 7 · Conclusiones consolidadas

### Resuelto tras leer las 6 hojas
| Pregunta v2/nuevo.md | Respuesta |
|---|---|
| **Q1** Granularidad de pluses | Por **(operario × día × proyecto)**. Lo cobra el cliente Y se paga al trabajador, con importes distintos. Ver §6 hallazgo 2/3. |
| **Q2** Tarifas con vigencia temporal | **NO**. El cliente crea un proyecto nuevo cada año con tarifa nueva. Ver §1 hallazgo "cambio de año". |
| **Q3** Extras distintas que normal | **NO** (al cobro al cliente). Labor=Ex Lab, Lab Noche=Ex Lab Noc, Fest=Ex Fes, Fest Noct=Ex Fes Noct **siempre**. Solo el coste al trabajador cambia. Ver §1 hallazgo "extras". |
| **Q4** Tarifa por defecto | **NO existe**. Proyecto sin entrada en TARIFAS = no se factura. 58 de 85 proyectos están así (internos). |
| **Q5** Desplaz / Ex Despl | Son **tipos de hora normales** (no pluses ni dietas). Apenas usados en la práctica. |
| **Q6** Mapeo 8 tipos → 5 tasas trabajador | Labor→TASA HORA, Lab Noche→TASA NOCT, Ex Lab→TASA EXTRA, Ex Lab Noc→TASA NOCT (¿o EXTRA?), Fest→TASA FEST, Ex Fes→TASA FEST, Fest Noct→TASA FEST NOCT, Ex Fes Noct→TASA FEST NOCT. Pendiente confirmar Ex Lab Noc. |
| **Q10** No facturable | `tarifa = 0` o ausencia de entrada en catálogo. |
| **Q11** Ausencias / Licencias / Vacaciones | Son **atributos como cualquier tipo de hora** (mismo formato de fila), pero con tarifa 0 y coste 0. No es un módulo aparte. |

### Nuevos hallazgos no previstos
1. **17 atributos posibles** (no 13 como creímos): 10 tipos de hora + 4 no-productivas + 3 pluses. Pueden añadirse más sin tocar schema si el modelo es relacional (opción B).
2. **Los pluses tienen DOS importes**: tarifa al cliente y coste al trabajador. Distinto al modelo "una sola tarifa".
3. **TIPO DIA (SI/NO)** se DERIVA del calendario + festivos. No se imputa manualmente.
4. **FESTIVO** lleva el NOMBRE del festivo. Se necesita catálogo de festivos en BD (16 festivos identificados en §3).
5. **El "TIPO PROYECTO"** del Excel ≠ `tipo_proyecto_id` de nuestra BD: es el **nombre/denominación** del proyecto. En nuestra BD = `proyectos.nombre`.

---

## 8 · Mapeo Excel → BD

### Entidades a crear / ampliar

| Excel | BD propuesta | Estado actual |
|---|---|---|
| TABLA DE EMPLEADOS (5 tasas) | `users` + 2 columnas (tasa_noche, tasa_festivo_noche) | **AMPLIAR**: solo hay 3 tasas |
| TARIFAS (proyecto × atributo → €) | Nueva tabla `tarifas_proyecto` | **NUEVO** |
| Pluses (cliente × plus → €) | Nueva tabla `pluses_cliente` con `tarifa_cliente` y `coste_trabajador` | **NUEVO** |
| Festivos catalogados | Nueva tabla `festivos` (fecha + nombre + ámbito nacional/local) | **NUEVO** |
| ENTRADA_DATOS/INPUT (parte diario) | Reformar `albaran_lineas_personal` a long format | **REFACTOR** |
| TipoHora enum | Ampliar de 4 a 10+ valores | **AMPLIAR** |

### Modelo propuesto (opción B confirmada)

```sql
-- Nueva tabla de atributos imputables (catálogo)
atributos_hora
  - id, codigo (Labor, Lab Noche, …, PLUS RETEN, VACACIONES)
  - nombre_corto, nombre_largo
  - tipo ENUM('hora_normal', 'hora_festiva', 'desplazamiento', 'no_productiva', 'plus')
  - se_paga_al_trabajador BOOL
  - mapeo_tasa ENUM('tasa_hora','tasa_extra','tasa_noche','tasa_festivo','tasa_festivo_noche','flat')

-- Catálogo tarifas
tarifas_proyecto
  - id, proyecto_id, atributo_id
  - importe DECIMAL(8,4)
  - UNIQUE(proyecto_id, atributo_id)

-- Pluses (coste fijo al trabajador)
pluses_coste_trabajador
  - id, atributo_id, importe DECIMAL(8,2)
  -- (60 € PLUS RETEN, 16 € PLUS FESTIVO …)

-- Festivos
festivos
  - id, fecha, nombre, ambito ENUM('nacional','autonomico','local')
  - localidad (nullable)

-- Líneas de albarán refactorizadas (long format)
albaran_lineas_personal (refactor)
  - id, albaran_id, user_id, proyecto_id
  - fecha DATE
  - atributo_id  -- antes era horas/horas_extra; ahora tipo único
  - cantidad DECIMAL(6,2)  -- horas o unidades de plus
  -- snapshots (Observer):
  - tarifa_snapshot DECIMAL(8,4)
  - tasa_trabajador_snapshot DECIMAL(8,4)
  - facturacion_snapshot DECIMAL(10,2)  -- cantidad × tarifa
  - coste_snapshot DECIMAL(10,2)  -- cantidad × tasa
```

### Cálculos derivados (no se guardan; vista o accesor)

```php
$linea->facturacion = $linea->cantidad * $linea->tarifa_snapshot;
$linea->coste = $linea->cantidad * $linea->tasa_trabajador_snapshot;
$linea->margen = $linea->facturacion - $linea->coste;
```

Para los pluses, el `tasa_trabajador_snapshot` viene del catálogo `pluses_coste_trabajador` (no de las 5 tasas del operario).

---

## 9 · Preguntas que quedan abiertas al cliente

Más reducidas tras el análisis:

1. **Coste del trabajador en pluses**: ¿de dónde sale el 60 € de PLUS RETEN y 16 € de PLUS FESTIVO? ¿Es constante por tipo de plus o cambia con el operario / proyecto?
2. **Ex Lab Noc**: ¿se paga al trabajador con TASA EXTRA o con TASA NOCT? (La tabla solo tiene 5 tasas, pero la combinación "extra de noche" podría ser cualquiera de las dos.) Lo mismo para Ex Fes Noct.
3. **Catálogo de pluses**: ¿hay más pluses planeados a futuro o solo los 3 actuales (PLUS RETEN, PLUS FESTIVO, PLUS NOCHE)?
4. **Festivos locales**: ¿qué localidades son relevantes? Hemos visto La Solana y Manzanares. ¿El cliente quiere gestionar el calendario por localidad o un único calendario general?
5. **Ausencias / Licencias / Bajas / Vacaciones**: ¿se quieren reportes específicos de estas categorías (control de absentismo) o solo descontar de las horas productivas? Si hay reportes, ¿qué métricas?
6. **Captura de datos**: para introducir el parte, ¿prefieren la vista wide (estilo ENTRADA_DATOS, con 10+ inputs por fila) o la vista long (una línea por tipo)? Recomiendo vista wide (más rápida de teclear) con conversión interna a long en la BD.

---

## 8 · Notas operativas

- Script de análisis: `_analyze_excel.php` en la raíz (temporal, se borra al terminar).
- Comando de lectura: `php _analyze_excel.php "<NOMBRE_HOJA>" <MAX_FILAS>`.
- PHPSpreadsheet **no resuelve** `Tabla_Tarifas[...]` (referencias estructuradas). Se lee valor cacheado vía `getOldCalculatedValue()`. Si una celda no tiene cache, sale como `[formula...]`.
- Memoria PHP subida a 4G (`ini_set('memory_limit', '4G')`).
