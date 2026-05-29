# Registro de errores corregidos (Fase 2)

Fecha: 2026-05-18
Estado: CERRADO

## Modulo Ajustes

1. Guardar no persistia cambios.
	- Causa: flujo de guardado y estructura de vista con problemas de enlace Livewire.
	- Correccion: ajuste de bindings, flujo de guardado y validacion centralizada.

2. Mensajes duplicados y UX confusa.
	- Causa: doble presentacion de errores/notificaciones.
	- Correccion: mensaje unico y limpio, errores por campo.

3. Scroll/maquetacion no deseada en tabs.
	- Causa: contenedor con overflow horizontal.
	- Correccion: eliminacion de overflow innecesario.

4. Campos de color y limites no consistentes.
	- Causa: validacion y restricciones incompletas.
	- Correccion: reglas y limites aplicados, defaults coherentes.

## Modulo Empresa

1. Botones Guardar/Deshacer no ejecutaban accion.
	- Causa principal: archivo Blade con BOM UTF-8 (EF BB BF), Livewire montando root incorrecto.
	- Correccion: reescritura en UTF-8 sin BOM + limpieza de cache.

2. No se veian datos de BBDD de forma fiable.
	- Causa: hidratacion Livewire afectada por el root mal montado.
	- Correccion: arreglado al eliminar BOM y estabilizar vista.

3. Doble notificacion de guardado.
	- Causa: flash global + flash local en la vista de Empresa.
	- Correccion: se mantiene solo el flash global (arriba).

4. Falta de limites visuales en inputs.
	- Causa: formulario sin maxlength en varios campos.
	- Correccion: maxlength y reglas alineadas con la BBDD para texto, telefono y email.

## Politica temporal de cambios

- Ajustes: 100% completado y estabilizado.
- Empresa: 100% completado y estabilizado.
- No tocar Ajustes/Empresa salvo motivo mayor (bug critico, seguridad, o cambio de negocio aprobado).

