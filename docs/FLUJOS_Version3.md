# 🔄 FLUJOS — ELECIND

## 1️⃣ Flujo: Crear albarán normal (móvil)

```
[Trabajador abre app móvil]
        ↓
[Login con username + password → menú principal]
        ↓
[Toca "Parte de Trabajo"]
        ↓
[Selecciona proyecto] ← filtrado por proyectos asignados al usuario
        ↓
[Selects dependientes se filtran]
   - Concepto (del proyecto + opción "Otro" → texto libre)
   - Responsable (asignados al proyecto)
   - Materiales (asignados al proyecto, con stock)
        ↓
[Elige tipo de hora] (laboral_dia/noche/festivo_dia/noche)
        ↓
[Pone Nº Horas + Fecha + Observaciones]
        ↓
[Marca "Añadir Compañeros"?]
   SÍ → añade N compañeros con sus horas (normales/extras opcionales)
   NO → solo él
        ↓
[Materiales: cantidad por lote]
        ↓
[Guarda] → albarán creado en estado "pendiente_firma"
        ↓
[Redirige a pantalla de Firmar]
        ↓
[Firma su campo (Canvas)]
        ↓
[Opciones]:
   - Firma también responsable (presente) → "cuenta_prestada"
   - Guarda y envía email al responsable → "token_email"
        ↓
[Si 2 firmas → estado "firmado"]
[Si 1 firma → estado "firmado_parcial"]
        ↓
[Notificación email a Elecind con copia del albarán]
[Stock se descuenta automáticamente de los lotes]
```

## 2️⃣ Flujo: Albarán personalizado (móvil)

```
[Trabajador toca "Parte personalizado"]
        ↓
[Formulario igual al normal PERO]:
   - Cliente: select + opción "Otro" (texto libre)
   - Tipo proyecto: select + opción "Otro"
   - Proyecto: select + opción "Otro"
   - Responsable: select + opción "Otro"
   - Concepto: select + opción "Otro"
   - Materiales: texto libre (no descuenta stock)
        ↓
[Guarda como "borrador"]
        ↓
[Permite firmar en estado borrador]
        ↓
[Email a admin de Elecind: "Borrador pendiente revisión"]
        ↓
[Admin abre desde web]
   - Vista con campos inteligentes
   - Cada "Otro" muestra select con valores existentes + texto + botón "+ Crear"
        ↓
[Admin completa/vincula datos]
   - Si crea nuevo cliente/responsable/etc → autosugerencia de username, avisos de duplicado por DNI/CIF/email
        ↓
[Admin cambia estado a "firmado" o "pendiente_firma"]
        ↓
[Stock se descuenta cuando se confirma]
```

## 3️⃣ Flujo: Crear usuario (admin desde web)

```
[Admin → Usuarios → + Añadir]
        ↓
[Escribe Nombre obligatorio: "Juan"]
        ↓
[Sistema sugiere username: "juan"]
   - Si ya existe: "juan.2", "juan.3", etc.
   - Admin puede sobrescribir
        ↓
[Pone contraseña obligatoria]
        ↓
[Campos opcionales: apellidos, email, dni, cif, telefono]
        ↓
[Selecciona tipo_usuario]:
   - "interno" → empleado de la empresa (NO empresa_cliente_id)
   - "externo" → responsable de cliente (selecciona empresa_cliente_id)
        ↓
[Selecciona rol] (filtrado por nivel ≤ propio)
        ↓
[Sistema valida]:
   - username único → si existe, error
   - email/dni/cif → si duplicado, MODAL aviso:
     "Ya existe un usuario con este DNI: Pedro García.
      ¿Quieres usarlo o crear uno nuevo?"
        ↓
[Admin confirma → usuario creado]
```

## 4️⃣ Flujo: Firma por token email

```
[Albarán creado, falta firma del responsable]
        ↓
[Sistema genera token UUID + hash]
   - Caducidad: 7 días (configurable)
   - Asociado a: albaran_id + firmante_asignado_id
        ↓
[Email al responsable con enlace único]
   "Tienes un albarán pendiente de firma: 
    https://elecind.getradi.es/firmar/{token}"
        ↓
[Responsable abre el enlace en su navegador]
        ↓
[Vista pública (sin login):
   - Info básica del albarán (cliente, fecha, horas, concepto)
   - Canvas para firmar SU campo
   - Datos del trabajador y firma del trabajador si ya firmó]
        ↓
[Firma → PNG guardado vía medialibrary]
        ↓
[Token marcado como usado → expira inmediatamente]
        ↓
[Si era la última firma pendiente → estado "firmado"]
        ↓
[Email a Elecind: "Albarán {ALB-2026-00032} firmado completo"]
```

## 5️⃣ Flujo: Modificación tras "terminado"

```
[Albarán en estado "terminado" → bloqueado]
        ↓
[Admin con permiso "albaranes.modificar_terminado" pulsa Modificar]
        ↓
[Modal de advertencia: 
   "Este albarán está terminado. 
    Si lo modificas, se notificará al cliente.
    ¿Continuar?"]
        ↓
[Confirma → albarán desbloqueado temporalmente]
        ↓
[Realiza cambios → Guardar]
        ↓
[Sistema]:
   - Vuelve a bloquear
   - Registra cambio en Activity Log
   - Envía email a Elecind con resumen de cambios
```

## 6️⃣ Flujo: Stock y entrada de material

```
[Compras → llega material nuevo]
        ↓
[Admin → Materiales → Entrada de stock]
        ↓
[Selecciona material o crea nuevo]
[Pone: proveedor, nº pedido, cantidad, fecha]
        ↓
[Sistema crea nuevo MaterialLote]
[Registra movimiento_stock tipo "entrada"]
        ↓
[Lote disponible para asignar a proyectos]
```

## 7️⃣ Flujo: Stock bajo

```
[Trabajador firma albarán con 5 tornillos]
        ↓
[Sistema descuenta 5 del lote elegido]
        ↓
[Sistema comprueba: stock_disponible <= stock_minimo?]
   SÍ Y notificar_stock_bajo=true:
        ↓
   [Email SOLO al admin del tenant (no superadmin)]
   [Incidencia automática tipo "stock_bajo"]
        ↓
   [Admin puede ver incidencia + gestionar entrada nueva]
```

## 8️⃣ Flujo: Ausencias

```
[Trabajador → móvil → Faltas de Asistencia]
        ↓
[Crea ausencia: tipo, fecha_inicio, fecha_fin, descripción]
   - Opcional: hora_inicio y hora_fin (medio día / horas)
        ↓
[Estado inicial: "pendiente" (configurable)]
        ↓
[Email al admin]
        ↓
[Admin → Web → Ausencias → Aprueba/Rechaza]
        ↓
[Email al trabajador con resolución]
        ↓
[Aparece en Control de Horas del trabajador]
```

## 9️⃣ Flujo: Incidencias

```
[Cualquiera puede crear incidencia desde menú "⋮" del móvil o desde web]
        ↓
[Tipos]:
   - Ligada a albarán (con contexto si se abre desde el albarán)
   - Ligada a ausencia
   - General
   - Stock_bajo (automática)
        ↓
[Campos: título, descripción, fotos]
        ↓
[Estado inicial: "abierta" (configurable)]
        ↓
[Admin gestiona desde web]:
   - Asignar a alguien
   - Cambiar estado
   - Adjuntar más info
        ↓
[Visible dentro del albarán/ausencia si está ligada]
[Listado general en sección Incidencias]
```

## 🔟 Flujo: Alta de nuevo tenant (Fase 6)

```
[Cliente paga suscripción en getradi.es (web SaaS)]
        ↓
[Web envía webhook a admin.getradi.es]
        ↓
[Sistema central recibe webhook:
   - Crea registro en `tenants`
   - Llama a API LucusHost: crear BD + subdominio
   - Aplica migraciones tenant
   - Crea usuario superadmin + admin con CIF como password temporal
     (username: cif del cliente)
   - Asigna plan + funcionalidades]
        ↓
[Email al cliente con: URL + credenciales + URL del formulario de configuración]
        ↓
[Cliente entra en {tenant}.getradi.es]
[Completa formulario de empresa + cambia contraseña]
[Sube logo, configura colores]
        ↓
[App lista para usar]
```