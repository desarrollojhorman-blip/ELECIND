# 🛠️ INSTALACIÓN LOCAL — XAMPP

Guía paso a paso para arrancar ELECIND en local con XAMPP en Windows.

## ✅ Requisitos previos

- **XAMPP** con PHP **8.3+** y MySQL.
- **Composer** instalado globalmente.
- **Node.js** 18+ y npm.
- **Git**.
- Editor: VS Code recomendado.

## 1️⃣ Clonar el repositorio

Abrir terminal en `C:\xampp\htdocs\`:

```bash
git clone https://github.com/desarrollojhorman-blip/ELECIND.git
cd ELECIND
git checkout develop
```

## 2️⃣ Instalar dependencias

```bash
composer install
npm install
```

## 3️⃣ Configurar `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con:

```env
APP_NAME=Elecind
APP_ENV=local
APP_URL=http://elecind.test
APP_DEBUG=true

# BD
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elecind
DB_USERNAME=root
DB_PASSWORD=

# Mail (rellenar después con LucusHost SMTP)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@elecind.com
MAIL_FROM_NAME="${APP_NAME}"

# Tokens de firma
FIRMA_TOKEN_DEFAULT_DIAS=7
```

## 4️⃣ Crear base de datos

Entrar en phpMyAdmin (`http://localhost/phpmyadmin`) y crear:

- `elecind`

## 5️⃣ Configurar virtual host (opcional pero recomendado)

### A. Editar `hosts` (como Administrador)

`C:\Windows\System32\drivers\etc\hosts`:

```
127.0.0.1   elecind.test
```

### B. Editar Apache vhosts

`C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName elecind.test
    DocumentRoot "C:/xampp/htdocs/ELECIND/public"
    <Directory "C:/xampp/htdocs/ELECIND/public">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/elecind-error.log"
    CustomLog "logs/elecind-access.log" common
</VirtualHost>
```

### C. Activar vhosts

En `C:\xampp\apache\conf\httpd.conf`, descomentar:

```
Include conf/extra/httpd-vhosts.conf
```

### D. Reiniciar Apache desde el panel de XAMPP.

> **Alternativa rápida sin vhost**: usar `php artisan serve` y acceder a `http://localhost:8000`.

## 6️⃣ Migraciones y seeders

```bash
php artisan migrate          # Crea tablas
php artisan db:seed          # Datos iniciales (usuarios, roles, etc.)
```

## 7️⃣ Compilar assets

```bash
npm run dev      # desarrollo (watch)
# o
npm run build    # producción
```

## 8️⃣ Acceder

| URL | Para |
|---|---|
| `http://elecind.test` | App Elecind (web + móvil) |

### Credenciales iniciales (seeder)

- **Superadmin**: usuario `superadmin` / contraseña `password`
- **Administrador**: usuario `admin` / contraseña `password`

⚠️ Cambiar contraseñas tras primer login. Login por **username**, no email.

## 🐛 Problemas frecuentes

### "Class 'X' not found"
```bash
composer dump-autoload
```

### Permisos storage
```bash
php artisan storage:link
```

### Caché
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Apache no levanta
- Comprobar puerto 80 libre (Skype, IIS).
- Mirar logs en `C:\xampp\apache\logs\error.log`.

### Subdominio no resuelve
- Verificar archivo `hosts` guardado como Administrador.
- Limpiar caché DNS: `ipconfig /flushdns`.

## 📦 Configuración SMTP (cuando esté lista)

Actualizar `.env`:

```env
MAIL_HOST=smtp.lucushost.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@elecind.com
MAIL_PASSWORD=tu-password
MAIL_ENCRYPTION=tls
```

Probar:
```bash
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('tu@email.com')->subject('Prueba'));
```

## 🚀 Listo

Si todo funciona → arrancar `npm run dev` y empezar a programar la fase actual.