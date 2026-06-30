# Seguridad de la aplicacion (VPS + Laravel)

## Objetivo
Esta guia define que cambiar para reducir riesgos reales en produccion.
Incluye servidor (VPS), despliegue (FTP/SFTP), aplicacion Laravel, usuarios con permisos, archivos subidos y correo SMTP.

## Aclaracion rapida: VPS no es FTP
- VPS: es el servidor completo donde corre la aplicacion.
- FTP/SFTP: es el metodo para transferir archivos al servidor.
- Recomendado: usar SFTP (SSH) y evitar FTP sin cifrado.

### Debo tener el mismo cuidado?
Si, el cuidado debe ser igual o mayor.
- Tener un VPS no te protege por defecto: te da control, pero tambien responsabilidad.
- Aunque los roles de la app (admin/superadmin) no tengan acceso SSH, una cuenta comprometida con privilegios puede causar dano fuerte en datos y operativa.
- El objetivo es seguridad por capas: servidor (VPS), acceso remoto (SFTP/SSH) y aplicacion (roles, archivos, secretos, auditoria).

## Prioridad alta (hacer primero)

### 1) No usar FTP plano; usar SFTP/SSH
Que cambiar:
- No usar FTP (puerto 21 sin cifrado) para subir archivos.
- Usar SFTP sobre SSH (puerto 22) o FTPS bien configurado.
- Desactivar acceso por password en SSH y usar clave publica/privada.

Por que:
- FTP puede exponer credenciales en red.
- SFTP cifra trafico y credenciales, evitando robo facil.

### 2) Endurecer acceso al VPS
Que cambiar:
- Crear usuario admin del sistema distinto de root.
- Desactivar login SSH de root (`PermitRootLogin no`).
- Activar firewall (permitir solo 22, 80, 443 y lo estrictamente necesario).
- Cambiar puerto SSH opcionalmente.
- Instalar fail2ban o proteccion equivalente para bloquear intentos repetidos.
- Mantener sistema y paquetes actualizados.

Por que:
- Reduce superficie de ataque automatizado al servidor.

### 3) HTTPS obligatorio
Que cambiar:
- Instalar certificado TLS (Lets Encrypt o proveedor equivalente).
- Redirigir todo HTTP a HTTPS.
- Activar cabeceras de seguridad basicas (HSTS, X-Frame-Options, X-Content-Type-Options).

Por que:
- Evita que sesiones y credenciales viajen en claro.

### 4) Proteger secretos (APP_KEY, DB, SMTP, API keys)
Que cambiar:
- Guardar secretos en `.env` del servidor, nunca en codigo.
- No mostrar secretos en claro desde paneles de ajustes.
- Si una clave se cambia desde la app, tratarla como write-only (se puede actualizar, no leer).
- Rotar credenciales si alguna vez se expusieron.

Por que:
- Si un atacante ve secretos, puede pivotar a correo, base de datos o servicios externos.

### 5) Cuentas con privilegios (admin/superadmin)
Que cambiar:
- Forzar contrasenas fuertes para cuentas con permisos altos.
- Activar 2FA para superadmin y admins.
- Limitar intentos de login y bloqueo temporal.
- Aplicar minimo privilegio: cada rol solo lo necesario.

Por que:
- Una cuenta privilegiada comprometida causa danos directos en la aplicacion.

## Archivos subidos (zona critica)

### 6) Validacion estricta de archivos
Que cambiar:
- Permitir solo tipos necesarios (lista blanca): `pdf`, `docx`, `jpg`, `jpeg`, `png`.
- Validar extension y tipo MIME real en backend.
- Limitar tamano maximo por archivo y por solicitud.
- Renombrar archivos con nombre aleatorio (UUID/hash), no usar nombre original.

Por que:
- Evita subida de contenido ejecutable o malicioso disfrazado.

### 7) Almacenamiento y acceso seguro de archivos
Que cambiar:
- Guardar archivos fuera del directorio publico cuando sean privados.
- Servir descargas mediante controlador con autorizacion (no URL publica directa).
- Bloquear ejecucion de scripts en directorios de subida.

Por que:
- Evita acceso no autorizado y reduce riesgo de ejecucion remota.

## Correo SMTP seguro

### 8) Configuracion SMTP
Que cambiar:
- Usar TLS siempre.
- Usar cuenta SMTP dedicada para la app.
- No mostrar password SMTP en claro desde la app.
- Limitar tasa de envio para evitar abuso.
- Configurar SPF, DKIM y DMARC en DNS del dominio.

Por que:
- Protege credenciales, evita spam y mejora entregabilidad.

## Base de datos y backups

### 9) Base de datos
Que cambiar:
- No exponer DB a internet si no es necesario (bind local/red privada).
- Usuario DB con permisos minimos.
- Password robusta y rotacion periodica.

Por que:
- Minimiza impacto si una credencial secundaria se filtra.

### 10) Copias de seguridad
Que cambiar:
- Backups automaticos de DB y archivos.
- Guardar copia externa (offsite).
- Probar restauracion periodica.

Por que:
- Sin prueba de restauracion, backup no garantiza recuperacion real.

## Laravel y app (recomendado)

### 11) Configuracion de produccion
Que cambiar:
- `APP_ENV=production`
- `APP_DEBUG=false`
- Configurar `SESSION_SECURE_COOKIE=true` en HTTPS.
- Revisar `LOG_LEVEL` para no registrar secretos.

Por que:
- Evita fugas de informacion sensible por errores o logs.

### 12) Dependencias y vulnerabilidades
Que cambiar:
- Revisar dependencias PHP/JS periodicamente.
- Aplicar actualizaciones de seguridad.
- Auditar paquetes con herramientas de seguridad del ecosistema.

Por que:
- Vulnerabilidades conocidas son vectores frecuentes de ataque.

### 13) Auditoria y trazabilidad
Que cambiar:
- Registrar eventos criticos: login, cambios de roles, cambios de ajustes, borrados masivos.
- Alertar actividad anomala (intentos fallidos, acciones peligrosas repetidas).

Por que:
- Detectar temprano reduce el tiempo de exposicion.

## Riesgos clave a vigilar (resumen rapido)
- Password debil en cuentas con permisos altos.
- Subida de archivos sin validacion fuerte.
- Secretos visibles en panel de ajustes.
- SSH/FTP mal configurado.
- APP_DEBUG activo en produccion.
- Falta de backups probados.

## Plan por fases

### Fase 1 (inmediata)
- Migrar a SFTP/SSH con claves.
- Forzar HTTPS.
- Activar politica fuerte de contrasena y 2FA para admins.
- Ocultar secretos (SMTP/API) como write-only.

### Fase 2 (esta semana)
- Endurecer subida de archivos (lista blanca + MIME + tamano + nombres aleatorios).
- Firewall + fail2ban + hardening SSH.
- Revisar permisos de roles (minimo privilegio).

### Fase 3 (este mes)
- Backups automatizados con prueba de restauracion.
- Alertas y auditoria de eventos criticos.
- Rutina mensual de actualizaciones de seguridad.

## Nota importante de modelo de amenazas
Que superadmin y admin sean solo roles de aplicacion (sin SSH al VPS) es correcto.
Eso reduce riesgo de toma directa del servidor por rol.
Pero una cuenta comprometida con privilegios altos puede causar dano serio en datos y operativa de la app.
Por eso los controles de identidad, permisos y trazabilidad son obligatorios.
