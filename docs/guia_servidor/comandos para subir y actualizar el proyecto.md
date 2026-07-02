# Comandos para subir y actualizar el proyecto

Esta guia deja un flujo repetible para subir nuevas versiones al servidor.

## 1) Preparar ZIP en local (Windows)

### Opcion recomendada (desde el repo local)

Abre PowerShell en la raiz del proyecto y ejecuta:

```powershell
Set-Location "C:\xampp\htdocs\CLIENTES\ELECIND"

# Limpieza opcional de builds viejos
if (Test-Path "public/build") { Remove-Item "public/build" -Recurse -Force }

# Build local (opcional si el servidor siempre compila)
npm run build

# ZIP limpio desde Git (sin .git, sin node_modules, sin vendor)
git archive --format=zip --output "C:\Users\PC7\Downloads\uceda-bellon-main.zip" HEAD
```

### Opcion alternativa (si bajas ZIP de GitHub)

1. Descarga el ZIP de GitHub.
2. Extrae el contenido.
3. Revisa que la carpeta raiz tenga archivos como `artisan`, `composer.json`, `package.json`.
4. Si vas a recomprimir manualmente, elimina carpetas pesadas/no necesarias:
	 - `vendor`
	 - `node_modules`
	 - `.git`
5. Comprime de nuevo asegurando estructura correcta:
	 - El ZIP debe contener el proyecto con raiz valida (donde existan `artisan` y `composer.json`).

## 2) Subir ZIP al servidor

Desde PowerShell local:

```powershell
scp "C:\Users\PC7\Downloads\uceda-bellon-main.zip" jhorman@212.227.169.102:/home/jhorman/uploads/
```

Si pide password varias veces y luego sube al 100%, es normal.

## 3) Desplegar en servidor

Conecta por SSH y ejecuta:

```bash
ssh jhorman@212.227.169.102
ls -lh /home/jhorman/uploads
sudo /usr/local/sbin/deploy-uceda-bellon /home/jhorman/uploads/uceda-bellon-main.zip
```

## 4) Validacion rapida post-deploy

```bash
cd /opt/entreredes/apps/uceda-bellon/current
php artisan about
php artisan optimize:clear
php artisan optimize
```

Validar logs si algo falla:

```bash
tail -n 120 /opt/entreredes/apps/uceda-bellon/current/storage/logs/laravel.log
sudo journalctl -u php8.3-fpm -n 120 --no-pager
sudo journalctl -u nginx -n 120 --no-pager
```

## 5) Checklist corto antes de cerrar

1. `deploy-uceda-bellon` termina en `OK despliegue completado`.
2. `php artisan about` muestra `Environment: production`.
3. Login y menu lateral funcionan en navegador.
4. Sin errores JS en consola.

## 6) Errores comunes y solucion corta

- Error de estructura ZIP (faltan `artisan`/`composer.json` en `current`):
	- Regenerar ZIP con `git archive` desde la raiz del repo.

- Error `public/storage link already exists`:
	- No bloquea el deploy, es informativo.

- Warning `lock file is not up to date`:
	- No suele bloquear deploy, pero conviene alinear `composer.lock` en desarrollo.

## Estado actual de la guia

Nos quedamos en la parte de verificar en produccion los errores que solo aparecen en el servidor, sobre todo los relacionados con Livewire, Alpine, HTTPS y las cookies/sesion.

El motivo de los problemas es que local y servidor no se comportan igual:

- En local normalmente trabajamos sin proxy, sin HTTPS real y con menos caché.
- En produccion Nginx, PHP-FPM, las cookies de sesion, APP_URL, el dominio real y los certificados HTTPS influyen mucho.
- Si Laravel no detecta bien el esquema o el host, las URLs firmadas de Livewire pueden fallar con `401 Unauthorized`.
- Tambien pueden aparecer errores por cargar Alpine o sus plugins dos veces, o por diferencias entre el build local y el build de produccion.

Por eso en el servidor estamos comprobando:

- `APP_URL`
- `SESSION_SECURE_COOKIE`
- `SESSION_DOMAIN`
- `SESSION_SAME_SITE`
- el vhost correcto de Nginx
- los logs de `uceda-bellon.access.log` y `uceda-bellon.error.log`

La idea es que esta guia sirva como referencia para subir nuevas versiones y, si vuelve a pasar algo parecido, saber por donde empezar a revisar.


local
PS C:\Users\PC7>


scp "C:\Users\PC7\Downloads\uceda-bellon-main\uceda-bellon-main.zip" jhorman@212.227.169.102:/home/jhorman/uploads/




servidor
jhorman@entreredes-enia:/opt/entreredes/apps/uceda-bellon/current$



ssh jhorman@212.227.169.102
ls -lh /home/jhorman/uploads
sudo /usr/local/sbin/deploy-uceda-bellon /home/jhorman/uploads/uceda-bellon-main.zip



cd /opt/entreredes/apps/uceda-bellon/current
php artisan about
php artisan optimize:clear
php artisan optimize
grep -R "plugin(persist)" resources/js public/build/assets




tail -n 120 /opt/entreredes/apps/uceda-bellon/current/storage/logs/laravel.log
sudo journalctl -u php8.3-fpm -n 120 --no-pager
sudo journalctl -u nginx -n 120 --no-pager





