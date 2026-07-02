# V2 — Cambios y estado de Partes/Tarifas · 17/06/2026

Documento de **checkpoint operativo**. Recoge lo que se ha hecho hasta ahora en
el bloque **Tarifas + Partes**, lo que falta para que **todo se pueda usar de
punta a punta**, y los puntos a **controlar** (riesgos / inconsistencias).

> Foco actual acordado: **tarifas y partes funcionando y usables**. Los informes
> (ver §5) quedan aparcados para el final — la estructura ya está montada pero no
> es prioridad.

Referencias previas:
- [`150626_inicio.md`](./150626_inicio.md) — arquitectura y plan de fases.
- [`160626_tarifa_partes.md`](./160626_tarifa_partes.md) — cierre de Fase 4. **⚠️ Su descripción de las líneas de parte (modelo *long* con `atributo_id` y economía por línea) está OBSOLETA** — ver §3.

---

## 1 · Giro de diseño importante (16-17/06)

El modelo de Partes se **reescribió como clon de Albaranes** (modelo *wide*),
abandonando el modelo *long* (una fila por atributo con snapshots económicos)
que describía el documento del 16/06.

| Antes (doc 16/06) | Ahora (real, en working tree) |
|---|---|
| `partes_lineas_personal` *long*: `atributo_id`, `cantidad`, `tarifa_snapshot`, `facturacion_snapshot`, `coste_snapshot` | *wide* estilo albarán: `horas`, `horas_extra` + 3 tasas snapshot (`tasa_hora/extra/festivo`). El tipo de jornada se hereda de la cabecera (`partes.tipo_hora`) |
| Cabecera mínima (operario, proyecto, fecha, horas) | Cabecera **idéntica a Albarán**: cliente, proyecto, concepto, responsable, fecha, `tipo_hora`, observaciones, modo "personalizado" con textos libres y todos los snapshots |
| Flag `es_albaran` autocompletado por Observer | **Columna eliminada**. El vínculo se deriva de `albaran_id IS NOT NULL`. La conversión es explícita: botón **"Generar albarán"** |
| Sin materiales en el parte | Nueva tabla `partes_lineas_material` (espejo de `albaran_lineas_material`) |

**Motivo del giro**: reutilizar al 100% el flujo, la UI y los observers de
Albaranes (cabecera + líneas por modales + snapshots), y que "Generar albarán"
sea un simple clonado parte→albarán. Menos código nuevo, menos riesgo.

**Consecuencia**: el cálculo fino por tipo de hora (las 8 tasas / atributos,
noche/festivo, pluses) **NO vive en la línea del parte** todavía. Eso es
justamente lo que queda por detallar cuando entremos al cálculo real (§4.4).

---

## 2 · Qué está hecho

### 2.1 · Bloque Tarifas (Fases 1-3) — sin cambios desde el 16/06

- `atributos_hora` (11 atributos sembrados), `tarifas_cliente`
  (`cliente, tipo_proyecto, atributo, importe`, UNIQUE), `tarifas_historial`
  (unificado cliente+trabajador).
- `users`: 8 tasas explícitas. `tipos_proyectos`: `genera_albaran_por_defecto`.
- Observers: `TarifaClienteObserver`, `UserTasasObserver`.
- 3 pantallas: `/tarifas/clientes`, `/tarifas/trabajadores`, `/tarifas/historial`.
- Integración en fichas de Cliente y Usuario (pestaña "Tarifas").
- Permisos `tarifas.*`.
- **Datos actuales**: 23 filas en `tarifas_cliente`.

### 2.2 · Bloque Partes — reescrito estilo albarán (este es el trabajo nuevo)

**Base de datos** (migraciones nuevas, sin commitear):
- `2026_06_16_100000_drop_es_albaran_from_partes.php` — elimina `es_albaran`.
- `2026_06_16_120000_align_partes_with_albaranes_schema.php` — alinea `partes`
  con el esquema de `albaranes` (cliente/concepto/responsable/tipo_hora/
  snapshots/personalizado…).
- `2026_06_16_120100_recreate_partes_lineas_personal_albaran_style.php` —
  recrea `partes_lineas_personal` *wide* (`horas`, `horas_extra` + 3 tasas).
- `2026_06_16_100100_create_partes_lineas_material_table.php` — líneas de
  material del parte (con snapshots; **no ajusta stock**).

**Modelos**:
- `Parte` — clon de `Albaran`. Estados `abierto`/`cerrado`. Relaciones
  cliente/proyecto/concepto/creador/responsable/albaran + `lineasPersonal`,
  `lineasMaterial`. Scopes `conAlbaran()`/`sinAlbaran()`. `horasTotales()`,
  `esEditable()`, `tieneAlbaran()`. LogsActivity.
- `ParteLineaPersonal` — `horas`, `horas_extra` + snapshots trabajador y 3 tasas.
- `ParteLineaMaterial` — `cantidad` + snapshots del material (descr., unidad,
  pedido, familia, precios coste/venta).

**Observers** (registrados en `AppServiceProvider`):
- `ParteObserver` — `creating`: número `PT-YYYY-NNNN`. `saving`: snapshots de
  cliente/proyecto/concepto/creador/responsable (regla `isDirty` por FK).
- `ParteLineaPersonalObserver` — snapshot trabajador + 3 tasas al cambiar
  `trabajador_id`.
- `ParteLineaMaterialObserver` — snapshot material al cambiar `material_id`.
  **No toca stock** (eso solo ocurre al generar el albarán).

**UI / Componentes**:
- `ParteForm` — cabecera albarán-style (incl. modo personalizado con textos
  libres). Las líneas NO van aquí (se editan por modales en `Editar`).
- `Partes\Index` — listado con filtros (operario, proyecto, cliente, estado,
  ¿con albarán?, rango fechas), búsqueda, orden, eliminación.
- `Partes\Editar` — tabs **Parte / Trabajadores / Materiales** (CRUD inline por
  modal) + botón **"Generar albarán"** (clona cabecera y líneas a `Albaran`,
  marca el parte `cerrado` y rellena `albaran_id`). Si el parte ya tiene
  albarán, queda en **solo lectura**.
- `Partes\Ver` — lectura.
- `PartePolicy` — view/update/delete; bloqueo si cerrado / con albarán.
- Permisos `partes.*`. Sidebar: **Partes → (Partes / Albaranes / Borradores /
  Informe de horas)**.

### 2.3 · Informe de horas (nuevo, este día — aparcado pero funcional)

- `Partes\Informe` + vista + `InformeHorasExport` (Excel). Ruta
  `/partes/informe`, ítem de menú bajo Partes.
- Rango de fechas (con atajos mes/año) + **2 niveles**: "Ver por"
  (trabajador/cliente/proyecto) y "Desglosar por" (sub-filas expandibles).
  Total general + export.
- **Cálculo provisional y centralizado** en `Informe::calcularLinea()`:
  - A pagar (coste) = `horas × tasa_hora + horas_extra × tasa_extra`.
  - A cobrar (factur.) = `(horas+horas_extra) × tarifa_labor` del proyecto
    (de `tarifas_cliente` para el atributo Labor; 0 si no hay tarifa).
  - Sin pluses, sin distinción noche/festivo. **Se cambiará** (§4.4).

---

## 3 · Estado de consistencia (lo que hay que CONTROLAR)

1. **Working tree sin commitear.** Todo el rewrite de Partes (+ informe) está sin
   commit. Archivos modificados/nuevos:
   - `M` ParteForm, Partes/{Editar,Index,Ver}, Models/{Parte,ParteLineaPersonal},
     Observers/{ParteObserver,ParteLineaPersonalObserver}, PartePolicy,
     AppServiceProvider, sidebar, vistas partes/{editar,index,ver}, routes/web.
   - `??` Exports/InformeHorasExport, Partes/Informe, Models/ParteLineaMaterial,
     Observers/ParteLineaMaterialObserver, 4 migraciones, informe.blade,
     este documento.
   - **Acción**: hacer commit en cuanto el flujo esté verificado (red de
     seguridad). Hoy NO hay tests que cubran esto.

2. **Docs desincronizados.** El 160626 describe el modelo *long* que ya no existe.
   Cuando se cierre este checkpoint, marcar en el 160626 que su §2 (Partes) quedó
   superado por el modelo *wide* de este documento.

3. **La línea de parte no tiene economía ni atributo.** Solo `horas`/`horas_extra`
   + 3 tasas (de las 8 que tiene `users`). El `tipo_hora` está en la cabecera, no
   por línea. → cualquier cálculo fino (8 atributos, noche/festivo, pluses) hoy
   **no es posible** desde la línea. Es la decisión de fondo pendiente (§4.4).

4. **Generar albarán y stock.** El parte NO ajusta stock; el albarán generado SÍ
   (vía `AlbaranLineaMaterialObserver`). Verificar que al generar no se duplica
   consumo y que las líneas espejo disparan bien sus snapshots.

5. **Cobertura de tarifas.** La facturación del informe depende de que exista
   `tarifas_cliente` para `(cliente, tipo_proyecto, Labor)`. Los partes de prueba
   actuales (cliente 6 / tipo 3) **no tienen tarifa** → "A cobrar" = 0 (correcto,
   pero conviene cargar tarifas reales para validar).

---

## 4 · Qué falta para que TODO SE PUEDA USAR (foco: tarifas + partes)

Ordenado por prioridad de usabilidad.

### 4.1 · Verificar el flujo completo de Partes end-to-end
- Crear parte → añadir trabajadores (horas/extra) → añadir materiales →
  **Generar albarán** → comprobar que el albarán nace bien (número, líneas,
  estado pendiente_firma) y el parte queda `cerrado` + `albaran_id`.
- Probar el modo "personalizado" (textos libres) y los bloqueos de la Policy.
- **Pendiente**: prueba manual guiada + corregir lo que aparezca.

### 4.2 · Vínculo bidireccional Parte ↔ Albarán (Fase 5, a medias)
- Hecho: parte → albarán (`albaran_id`, "Generar albarán").
- Falta: en **Albarán/Ver** mostrar enlace "Ver parte de origen"; en
  **Parte/Ver** enlace al albarán generado.
- Falta (opcional, riesgo medio): backfill de albaranes históricos → su parte
  espejo. Solo si se quiere la vista unificada.

### 4.3 · Tarifas usables al 100%
- Confirmar que `Tarifas → Clientes` permite dar de alta TODAS las combinaciones
  reales que el cliente factura (cliente × tipo_proyecto × atributo), incluido
  crear filas para tipos de proyecto nuevos.
- Cargar las tarifas reales (del Excel) para poder validar cobro/coste.

### 4.4 · DECISIÓN DE FONDO: modelo de cálculo de horas (lo que el cliente llama "detallar")
La pregunta que lo condiciona todo:

> **¿El parte se queda *wide* (horas/horas_extra + `tipo_hora` de cabecera) o
> necesita el detalle por atributo (las 8: Labor, Lab Noche, Fest, Fest Noct,
> Ex Lab, Ex Lab Noc, Ex Fes, Ex Fes Noct) por línea de trabajador?**

- El Excel del cliente trabaja en **detalle por atributo** (modelo long). Para
  calcular bien "cuánto pagar / cuánto cobrar" por tipo de hora se necesita ese
  detalle.
- Hoy el parte solo distingue normal vs extra y un único `tipo_hora` por
  cabecera. Eso **no** reproduce el Excel.
- Opciones: (A) añadir columnas por atributo a la línea, (B) volver al modelo
  long por atributo, (C) híbrido. **Esto es lo siguiente a decidir** antes de
  tocar el cálculo del informe.

### 4.5 · Pluses (aparcados a propósito)
- Coste al trabajador de los 3 pluses sigue **bloqueado por respuesta del
  cliente** (constante / por cliente / por trabajador). No tocar hasta confirmar.

---

## 5 · Informes (aparcado para el final)
Estructura ya montada (§2.3). Cuando se retome: replicar las hojas `informe` /
`informe (2)` / `FABRICACIÓN` del Excel (rentabilidad por cliente/proyecto/
trabajador, mensual/anual, trabajo interno sin tarifa). Depende de §4.4 para que
los números cuadren con su Excel.

---

## 6 · Preguntas abiertas al cliente
1. **Detalle por atributo** (§4.4): ¿wide o por los 8 atributos? — **bloquea el
   cálculo real**.
2. Coste de los pluses (constante / por cliente / por trabajador).
3. Mapeo exacto `Ex Lab Noc` / `Ex Fes Noct` → tasa.
4. "Mes Exide" (mes fiscal): ¿cómo se define el corte? (para informes mensuales).
5. Festivos locales (La Solana, Manzanares…) — para v3.

---

## 7 · Próximo paso recomendado
1. **Probar Partes end-to-end** (§4.1) y arreglar lo que falle.
2. **Commit** del bloque Partes albarán-style.
3. Cerrar el **vínculo bidireccional** (§4.2) y la **carga de tarifas reales** (§4.3).
4. Sentarse a decidir el **modelo de cálculo** (§4.4) antes de detallar nada del
   pago/cobro.

Este documento no se reescribe; los cambios siguientes van en un `.md` nuevo de
esta carpeta.
