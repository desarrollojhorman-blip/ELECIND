# V2 — Control de pruebas

Checklist vivo de qué se ha verificado y qué no. Se marca aquí cada control que
se da por bueno para no volver a repasarlo. Para una revisión rápida, mirar la
columna **Estado**.

**Foco actual:** solo **PARTES** — que se creen bien y contabilicen bien tarifas,
horas y todo lo demás. Albaranes/borradores solo en lo que toca a los partes.

**Leyenda de estado:**
- ✅ **Terminado** — probado y confirmado. No se toca salvo cambio de lógica de negocio.
- 🟡 **Implementado** — hecho en código, pendiente de tu prueba manual.
- ⬜ **Pendiente** — aún sin hacer.

---

## Móvil

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Crear parte de trabajo (compañeros, materiales, plus retención, solo albarán, parte + albarán) | ✅ Terminado | 02/07/2026 | Crea y registra bien todas las combinaciones. Cerrado salvo cambio de lógica. |
| Listado (gestión de partes): tarjeta muestra su relación de origen (albarán→parte, parte→borrador) | 🟡 Implementado | 02/07/2026 | Línea pequeña al pie de la tarjeta, solo informativa. Relaciones verificadas. |

---

## Web — Partes

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Crear parte (cabecera + trabajadores + materiales) | ⬜ Pendiente | | |
| Editar parte (modales trabajadores/materiales, costes/gastos) | ⬜ Pendiente | | |
| Ver parte (incl. pestaña Costes/Gastos solo lectura) | ⬜ Pendiente | | |
| Generar albarán desde parte (enlaza y cierra parte) | ⬜ Pendiente | | |

---

## Borradores → Parte

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Convertir borrador SIN albarán → crea solo parte | 🟡 Implementado | 02/07/2026 | Refactor a servicio `GeneradorAlbaran`; pendiente prueba manual. |
| Convertir borrador CON albarán → crea parte + albarán enlazados | 🟡 Implementado | 02/07/2026 | Antes creaba albarán huérfano; ahora parte primero. Pendiente prueba manual. |

---

## Tarifas y cálculo económico (en el parte)

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Snapshot de tarifa (cobro) y tasa (coste) por línea al asignar trabajador | ⬜ Pendiente | | Verificar con tarifas reales cargadas. |
| Cálculo facturación / coste / margen por línea y totales | ⬜ Pendiente | | |
| Plus retención en el cálculo | ⬜ Pendiente | | Confirmar si entra en el total. |

---

## Horas

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Control de Horas suma de albaranes + partes SIN albarán (sin doble conteo) | ⬜ Pendiente | | El código ya lo hace así; falta validar con datos. |

---

## Regla parte ↔ albarán

| Qué se controla | Estado | Fecha | Notas |
|---|---|---|---|
| Borrar albarán = definitivo (sin papelera) y reabre el parte | 🟡 Implementado | 02/07/2026 | Verificado con tinker; pendiente tu prueba en UI. |
| Bloqueo: albarán firmado/facturado no se puede borrar | ✅ Terminado | 02/07/2026 | Ya lo garantizaba `AlbaranPolicy` (solo borra si pendiente_firma). |
