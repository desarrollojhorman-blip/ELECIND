# Guia BBDD Laravel (ELECIND)

## Ruta base desde la que se ejecuta todo

Trabajamos siempre desde la raiz del proyecto:

D:\xampp\htdocs\CLIENTES\ELECIND

Si no estas en esa carpeta, entra primero:

cd D:\xampp\htdocs\CLIENTES\ELECIND

---

## Situacion 1: Proyecto recien clonado o recien iniciado (local)

### Comandos (en orden)

composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan migrate:status

### Que hace cada comando

- composer install
Instala las dependencias PHP del proyecto.

- copy .env.example .env
Crea el archivo de entorno local.

- php artisan key:generate
Genera APP_KEY para cifrado/sesiones.

- php artisan migrate:fresh --seed
Borra todas las tablas, vuelve a ejecutar todas las migraciones y carga seeders.
Ideal para levantar un entorno local limpio desde cero.

- php artisan migrate:status
Verifica que todas las migraciones quedaron en Ran.

---

## Situacion 2: Acabo de hacer pull y solo quiero actualizar BBDD sin borrar datos (local)

### Comandos (en orden)

php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan migrate:status

### Que hace cada comando

- php artisan optimize:clear
Limpia caches (config, rutas, vistas, etc.) para evitar inconsistencias tras cambios.

- php artisan migrate
Ejecuta solo migraciones pendientes. No borra tablas existentes.

- php artisan db:seed
Ejecuta el DatabaseSeeder (y los seeders registrados dentro).

- php artisan migrate:status
Comprueba que no quedaron migraciones en Pending.

Nota: si tus seeders son de demo y no quieres re-sembrar todo, ejecuta solo uno concreto:

php artisan db:seed --class=NombreSeeder

---

## Situacion 3: Quiero resetear mi local porque todo esta desalineado

### Comandos (en orden)

php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan migrate:status

### Que hace cada comando

- php artisan optimize:clear
Limpia estado cacheado antes del reseteo.

- php artisan migrate:fresh --seed
Resetea totalmente la base local y vuelve a crearla con datos seed.

- php artisan migrate:status
Validacion final de estado.

Atencion: este flujo elimina todos los datos locales actuales.

---

## Situacion 4: Solo he creado un seeder nuevo y quiero ejecutarlo

### Comando

php artisan db:seed --class=NombreSeeder

### Que hace

Ejecuta solo ese seeder, sin tocar migraciones ni borrar datos.

---

## Situacion 5: He creado una migracion nueva y quiero aplicarla en local

### Comandos (en orden)

php artisan make:migration nombre_de_migracion
php artisan migrate
php artisan migrate:status

### Que hace cada comando

- php artisan make:migration nombre_de_migracion
Genera el archivo de migracion.

- php artisan migrate
Aplica la nueva migracion.

- php artisan migrate:status
Confirma que quedo ejecutada.

---

## Situacion 6: Produccion (nunca borrar datos)

### Comandos recomendados de despliegue

composer install --no-dev --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --class=SeederConcreto --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart

### Que hace cada comando

- composer install --no-dev --optimize-autoloader
Instala dependencias para entorno productivo sin paquetes de desarrollo.

- php artisan optimize:clear
Limpia caches antes de regenerarlas.

- php artisan migrate --force
Aplica migraciones pendientes en produccion (sin pedir confirmacion interactiva).

- php artisan db:seed --class=SeederConcreto --force
Ejecuta solo seeders necesarios y controlados para produccion.

- php artisan config:cache / route:cache / view:cache
Regenera caches para mejor rendimiento.

- php artisan queue:restart
Reinicia workers para que tomen el codigo nuevo.

Importante en produccion:

- Nunca ejecutar migrate:fresh
- Nunca ejecutar migrate:reset
- No modificar migraciones antiguas ya ejecutadas
- Si falta una columna/cambio nuevo, crear una migracion nueva de parche

---

## Situacion 7: Quiero comprobar rapidamente el estado actual

### Comandos utiles

php artisan migrate:status
php artisan about

### Que hace cada comando

- php artisan migrate:status
Muestra que migraciones estan Ran o Pending.

- php artisan about
Muestra informacion del entorno Laravel (version, drivers, cache, etc.).

---

## Mini regla rapida

- Local con datos importantes: migrate + seed selectivo
- Local roto o desalineado: migrate:fresh --seed
- Produccion: migrate --force y seeders concretos
