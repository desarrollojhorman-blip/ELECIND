# V2 — Gestión de tarifas (análisis inicial, 12/06/2026)

Primera nota de la **Versión 2**. Recoge el análisis del modelo de tarifas que usa el cliente (Excel real) y la brecha con lo que tiene hoy la aplicación. **Aún no se ha tocado código.** El paso siguiente es responder las 6 preguntas del §6 para fijar el modelo de datos.

---

## 1 · Qué se quiere gestionar

Dos lados independientes pero usando los mismos **tipos de hora**:

| Lado | Significa | Quién lo paga |
|---|---|---|
| **TARIFAS** | Lo que la empresa **COBRA al cliente** por cada hora de trabajo realizado en uno de sus proyectos | Cliente paga a Elecind |
| **TASAS** | Lo que la empresa **PAGA al trabajador** por cada hora trabajada | Elecind paga al empleado |

Si un trabajador hace 100 horas para EXIDE, hay que: (a) cobrarle al cliente la tarifa pactada y (b) pagarle al trabajador su tasa pactada.

---

## 2 · Tipos de hora identificados (del Excel del cliente)

Tres grupos:

### Grupo 1 · Horas normales (por hora)
- `laboral`
- `laboral_noche`
- `festivo`
- `festivo_noche`

### Grupo 2 · Horas extras (por hora)
- `extra_laboral`
- `extra_laboral_noche`
- `extra_festivo`
- `extra_festivo_noche`

### Grupo 3 · Pluses (FLAT, no por hora)
- `plus_retencion`
- `plus_festivo`
- `plus_noche`
- Posiblemente más

Los pluses **no se multiplican por horas** — son importes fijos. Y son **específicos por cliente**:
- `PLUS RETEN` → solo EXIDE
- `PLUS FESTIVO` → solo VESTAS
- `PLUS NOCHE` → solo VESTAS

Pendiente confirmar (ver §6.1): si los pluses son por línea, por jornada, por albarán o por día.

> En la leyenda izquierda del Excel aparecen también `Desplaz` y `Ex Despl` (desplazamientos / extras de desplazamiento) — pendiente confirmar si son otro tipo de hora, de plus, o un grupo aparte. Ver §6.5.

Total: **8 tipos de hora "calculables" + N pluses**.

---

## 3 · Lado CLIENTE — tarifas (Imagen 1 del Excel)

### Estructura observada

Una fila por combinación:

```
(Cliente × Proyecto × Tipo Proyecto × Tipo Hora) → Tarifa €/h
```

### Ejemplos extraídos

| Cliente | Proyecto | Tipo Proy | Tipo Hora | Tarifa |
|---|---|---|---|---|
| EXIDE TECHNOLOGIES (2) | 25PR-1-2 | MANTENIMIENTO | Labor / Ex Lab / Lab Noche / Ex Lab Noc | 22,54 €/h |
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | Fest / Ex Fes / Fest Noct / Ex Fes Noct | 24,77 €/h |
| EXIDE (2) | 25PR-1-2 | MANTENIMIENTO | **PLUS RETEN** | **75,00 €** (importe fijo) |
| EXIDE (2) | 25PR-2-2 | LIMPIEZA | Labor / Ex Lab / … | 20,54 €/h |
| EXIDE (2) | 25PR-2-2 | LIMPIEZA | Fest / Ex Fes / … | 23,85 €/h |
| EXIDE (2) | 25PR-3-2 | INGENIERIA | Labor / Ex Lab / … | 22,54 €/h |
| VESTAS MANUFACTURING (22) | 26PR-4-22 | MANTENIMIENTO | Labor / Ex Lab / Fest / Ex Fes | 21,85 €/h |
| VESTAS (22) | 26PR-5-22 | OBRAS | Labor / Ex Lab | 21,85 €/h |
| VESTAS (22) | 26PR-5-22 | OBRAS | Fest / Ex Fes | 23,95 €/h |
| LOS DESMONTES (25) | 26PR-8-25 | OBRAS | Labor / Ex Lab | 23,00 €/h |
| LOS DESMONTES (25) | 26PR-8-25 | OBRAS | Fest / Ex Fes | 25,00 €/h |

### Observaciones clave

1. **La tarifa cambia por proyecto Y por tipo de proyecto.** EXIDE en MANTENIMIENTO cobra 22,54 €/h, en LIMPIEZA cobra 20,54 €/h, en INGENIERIA 22,54 €/h. La tarifa cuelga del **proyecto**, no solo del cliente.
2. **Laboral y Extra-laboral suelen cobrarse igual al cliente** (mismo importe en la misma fila del proyecto). Festivo y Extra-festivo también. Las "extras" no aplican multiplicador al cobrar al cliente. Confirmar en §6.3.
3. **Los pluses son específicos por cliente** y se cobran como **importe fijo**, no por hora. EXIDE tiene `PLUS RETEN = 75 €`; VESTAS tiene otros pluses con valores propios.
4. **`tipo_proyecto`** ya existe en la BD (`proyectos.tipo_proyecto_id`). El proyecto ya hereda su tipo, así que **la tarifa puede colgar solo de `(proyecto_id, tipo_hora)`** y el tipo de proyecto sale solo por relación. No hace falta replicarlo como FK en la tabla de tarifas.

---

## 4 · Lado TRABAJADOR — tasas (Imagen 2 del Excel)

### Estructura observada

```
(Trabajador × Tipo Hora) → Tasa €/h
```

Cada empleado tiene **5 tasas** distintas:

| Columna del Excel | Significa |
|---|---|
| **TASA HORA** | Tasa laboral base (diurna) |
| **TASA EXTRA** | Tasa de horas extras |
| **TASA NOCT** | Tasa nocturna |
| **TASA FEST** | Tasa festivo |
| **TASA FEST NOCT** | Tasa festivo nocturno |

### Ejemplos

| Nº Op | Nombre | Tasa hora | Extra | Noct | Fest | Fest Noct |
|---|---|---|---|---|---|---|
| 2 | JIMENEZ BELLON PEDRO-MANUEL | 35,000 | 11,000 | 11,000 | 13,000 | 13,000 |
| 13 | MUÑOZ GARCIA LUIS-MIGUEL | 23,214 | 12,000 | 12,000 | 14,000 | 14,000 |
| 49 | PASCUAL CALATRAVA DAVID | 20,235 | 11,250 | 13,333 | 13,333 | 15,000 |
| 139 | GAVAN MIHAI | 18,000 | 11,000 | 11,000 | 13,000 | 13,000 |

### Observaciones clave

1. **Solo 5 tasas distintas por trabajador**, no las 8 del lado del cliente. El trabajador "agrupa" extra+nocturna en `TASA NOCT` o `TASA EXTRA` según el caso. Pendiente confirmar el mapeo exacto (ver §6.6).
2. **Los importes son muy distintos del cobro al cliente**:
   - Tasa hora del empleado: 18-35 €/h (la mayoría 19-23)
   - Tarifa cobrada al cliente: 20-25 €/h
   - **El margen es la diferencia** (puede ser positivo o negativo según el empleado y el proyecto).
3. **Los pluses no aparecen en la tabla del trabajador** → al trabajador no se le pagan pluses individuales. Los pluses son solo para cobrar al cliente. Confirmar en §6.1.
4. **El nº de operario** de la columna izquierda encaja con el campo `numero_empleado` que ya existe en `users`.

---

## 5 · Brecha con lo que tenemos hoy en la app

### Lo que YA existe
- `users.tasa_hora`, `users.tasa_extra`, `users.tasa_festivo` → **solo 3 tasas por trabajador**.
- `TipoHora` enum con 4 valores: `laboral`, `laboral_noche`, `festivo`, `festivo_noche`.
- En `albaran_lineas_personal`: dos columnas `horas` + `horas_extra` en la misma línea — mezcla "normal y extra del mismo tipo" en una sola fila.
- **No existe nada del lado "cobrar al cliente"** — no hay tabla de tarifas por proyecto ni catálogo de pluses.

### Lo que FALTA

**Lado "cobrar al cliente" (nuevo):**
1. Tabla `tarifas_proyecto` (o similar) con `(proyecto_id, tipo_hora, importe)` con UNIQUE compuesto.
2. Tabla `pluses_cliente` (o similar) con `(cliente_id, tipo_plus, importe)` — porque los pluses son por cliente, no por proyecto, y son importes fijos.
3. Añadir los 4 tipos de hora "extra" al enum `TipoHora`.

**Lado "pagar al trabajador" (ampliar):**
4. Pasar de 3 a 5 tasas en `users`: añadir `tasa_noche` y `tasa_festivo_noche`. Migración + form.
5. Lógica de mapeo `tipo_hora → tasa del trabajador`:
   - `laboral` → `tasa_hora`
   - `laboral_noche` → `tasa_noche`
   - `extra_laboral` → `tasa_extra`
   - `extra_laboral_noche` → ¿`tasa_extra` o `tasa_noche`? (confirmar §6.6)
   - `festivo` → `tasa_festivo`
   - `festivo_noche` → `tasa_festivo_noche`
   - `extra_festivo` → ¿?
   - `extra_festivo_noche` → ¿?

**Sobre las líneas de albarán:**
6. Decidir si `albaran_lineas_personal` mantiene el patrón `horas + horas_extra` o si se rompe en una línea por tipo_hora. Posibilidades:
   - **Opción A**: una línea por tipo_hora trabajado (más flexible, encaja con el modelo del Excel).
   - **Opción B**: mantener `horas + horas_extra` y derivar "extra de qué" según `tipo_hora` de la cabecera (más simple pero menos preciso).

---

## 6 · Preguntas pendientes (responder antes de fijar modelo)

1. **¿Los pluses son por línea de albarán (por trabajador), por albarán entero (uno por jornada) o por día/semana?**  Y: ¿se le pagan también al trabajador o solo se cobran al cliente?
2. **¿Las tarifas pueden tener fecha de validez?** Si en algún momento se sube la tarifa de "MANTENIMIENTO" de 22,54 a 24, ¿los albaranes nuevos cobran 24 y los antiguos siguen con 22? Si sí, necesitamos `vigente_desde`/`vigente_hasta` en las tarifas (y snapshots ya hechos en los albaranes firmados).
3. **¿"Extra noche" en el COBRO al cliente es lo mismo que "laboral noche"?** El Excel sugiere que sí (mismo importe en la misma fila). Confirmar definitivamente.
4. **¿Hay tarifa por defecto cuando un proyecto no tiene tarifa específica?** ¿O un proyecto sin tarifa = no se puede facturar?
5. **¿"Desplaz" y "Ex Despl"** (que aparecen en la leyenda izquierda de la Imagen 1) son otro tipo de hora, otro plus, u otra categoría aparte (km, dietas)?
6. **Tasa NOCT del trabajador (Imagen 2): ¿se aplica solo a `laboral_noche` o también a `festivo_noche`?**  Mirando el Excel, parece que `TASA FEST NOCT` es independiente, por lo que `TASA NOCT` aplicaría solo a `laboral_noche` y las extras de noche. Confirmar.

---

## 7 · Próximos pasos

1. **Tú respondes las 6 preguntas del §6.**
2. **Propongo el modelo de datos completo** (tablas + columnas + enums + lógica de cálculo) sin código aún. Validas la arquitectura.
3. **Una vez validada**, hacemos el plan por fases:
   - Fase A: ampliar enum `TipoHora` + tasas del trabajador (migración + form).
   - Fase B: tabla de tarifas por proyecto + CRUD.
   - Fase C: tabla de pluses por cliente + CRUD.
   - Fase D: integración en el cálculo de líneas de albarán (cobro al cliente).
   - Fase E: integración en el cálculo de nóminas / informe de horas (pago al trabajador).
   - Fase F: exportaciones y reportes.

---

## 8 · Pendiente

- [ ] Respuestas del §6.
- [ ] Confirmar si las imágenes del Excel son completas o hay más columnas a la derecha que no han llegado a captura.
- [ ] Confirmar si "Tasa noct" y "Tasa fest noct" del Excel ya cubren también las extras de esos tipos o las extras se calculan con otra fórmula (`extra = tasa_extra` siempre, independientemente de si es noche o festivo).
