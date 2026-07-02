# Configurar HTTPS con certificados (instalación desde cero)

> Procedimiento estándar para dejar un proyecto (ej. **DULYMAZ**) funcionando con HTTPS
> en una máquina Windows / XAMPP, tanto en el servidor de desarrollo (**Vite**) como en
> **Apache**. Sirve para una instalación nueva o para montarlo en una máquina/VM limpia.
>
> Ejemplo usado en toda la guía:
> - **IP de la máquina:** `192.168.1.24`
> - **Proyecto:** `C:\xampp\htdocs\DULYMAZ`
>
> Sustituye la IP y la ruta por las tuyas donde aparezcan.

---

## 0. Requisitos previos

- **XAMPP** instalado en `C:\xampp`.
- **Node / npm** instalado (para Vite).
- **mkcert** descargado como `C:\mkcert\mkcert.exe`
  (herramienta que crea certificados de confianza local).
- La máquina debe tener **IP fija** en la misma subred que el router.

### 0.1. Fijar la IP de la máquina (si no la tiene)

En **Red e Internet → Ethernet → Asignación de IP → Editar → Manual**:

| Campo              | Valor de ejemplo      |
|--------------------|-----------------------|
| Dirección IP       | `192.168.1.24`        |
| Máscara de subred  | `255.255.255.0`       |
| Puerta de enlace   | `192.168.1.1`         |
| DNS preferido      | `8.8.8.8`             |
| DNS alternativo    | `8.8.4.4`             |

> Los tres primeros números de la IP y de la puerta de enlace deben coincidir con los del
> router. Comprueba la IP real con `ipconfig` (línea "Dirección IPv4" del adaptador Ethernet).

---

## 1. Instalar la CA raíz de mkcert (una sola vez por máquina)

En una consola (CMD o PowerShell):

```
C:\mkcert\mkcert.exe -install
```

Debe decir *"The local CA is ... installed in the system trust store"*.
Esto es lo que hace que el navegador **confíe** en los certificados que generes después.

---

## 2. Certificado para Vite

1. Crear (si no existe) la carpeta `cert` dentro del proyecto e ir a ella:
   ```
   cd C:\xampp\htdocs\DULYMAZ\cert
   ```

2. Generar el certificado para la IP de la máquina:
   ```
   C:\mkcert\mkcert.exe 192.168.1.24 localhost 127.0.0.1
   ```
   Crea dos ficheros:
   - `192.168.1.24+2.pem`      (certificado)
   - `192.168.1.24+2-key.pem`  (clave)

---

## 3. Configurar `vite.config.js`

Fichero: `C:\xampp\htdocs\DULYMAZ\vite.config.js`

Asegúrate de importar `fs` y `path` arriba y de dejar el bloque `server` así
(con la IP de tu máquina):

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import fs from 'fs'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        https: {
            key: fs.readFileSync(path.resolve(__dirname, 'cert/192.168.1.24+2-key.pem')),
            cert: fs.readFileSync(path.resolve(__dirname, 'cert/192.168.1.24+2.pem')),
        },
        hmr: {
            host: '192.168.1.24',
            protocol: 'wss',
        },
    },
});
```

Arrancar y comprobar:
```
cd C:\xampp\htdocs\DULYMAZ
npm run dev
```
Debe mostrar `Network: https://192.168.1.24:5173/` **sin errores** de certificado.

---

## 4. Configurar el `.env`

Fichero: `C:\xampp\htdocs\DULYMAZ\.env`

`APP_URL` debe apuntar al sitio por HTTPS con la IP de la máquina:
```
APP_URL=https://192.168.1.24
```
Revisa también, si existen, `ASSET_URL`, `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`, etc.

Comprobar rápido:
```
findstr /n "192.168 APP_URL" C:\xampp\htdocs\DULYMAZ\.env
```
Al arrancar Vite, la línea final debe decir `APP_URL: https://192.168.1.24`.

---

## 5. Certificado y configuración de Apache (XAMPP)

Apache sirve el sitio por HTTPS (puerto 443) y necesita **su propio certificado** en
`C:\xampp\apache\conf\ssl\`.

1. Generar el certificado en la carpeta ssl de Apache:
   ```
   cd C:\xampp\apache\conf\ssl
   C:\mkcert\mkcert.exe 192.168.1.24 localhost 127.0.0.1
   ```

2. Editar `C:\xampp\apache\conf\extra\httpd-ssl.conf` y dejar el VirtualHost con la IP
   y los certificados correctos:
   ```
   ServerName 192.168.1.24
   SSLCertificateFile "C:/xampp/apache/conf/ssl/192.168.1.24+2.pem"
   SSLCertificateKeyFile "C:/xampp/apache/conf/ssl/192.168.1.24+2-key.pem"
   ```

3. Verificar la sintaxis:
   ```
   C:\xampp\apache\bin\httpd.exe -t
   ```
   Debe decir `Syntax OK`.

4. Reiniciar Apache desde el **Panel de Control de XAMPP**: Apache → **Stop** → **Start**.

5. Probar en el navegador:
   ```
   https://192.168.1.24
   ```
   Debe cargar **con candado** y **sin aviso de certificado**.

---

## 6. Comprobación final (checklist)

- [ ] IP fija correcta (misma subred que el router) y con internet.
- [ ] `mkcert -install` ejecutado (CA raíz de confianza).
- [ ] Certificado de Vite generado en `DULYMAZ\cert\`.
- [ ] `vite.config.js` con `https` + `hmr` apuntando a la IP y a los `.pem`.
- [ ] `.env` con `APP_URL=https://<IP>`.
- [ ] Certificado de Apache generado en `apache\conf\ssl\`.
- [ ] `httpd-ssl.conf` con `ServerName` + `SSLCertificateFile` + `SSLCertificateKeyFile`.
- [ ] `httpd.exe -t` = `Syntax OK` y Apache reiniciado.
- [ ] `https://<IP>` abre con candado y `https://<IP>:5173` (Vite) sin errores.

---

## 7. Qué hay que rehacer si cambia la IP (mudanza / red nueva)

Los certificados están **atados a la IP** con la que se generaron. Si la IP cambia, el
certificado deja de coincidir y el navegador da error. Hay que repetir con la IP nueva:

1. Fijar la nueva IP en la máquina (paso 0.1).
2. Regenerar el certificado de Vite (paso 2) y actualizar `vite.config.js` (paso 3).
3. Actualizar `APP_URL` en el `.env` (paso 4).
4. Regenerar el certificado de Apache y actualizar `httpd-ssl.conf` (paso 5).
5. Reiniciar Apache y comprobar.

Truco para localizar todas las referencias a una IP vieja:
```
findstr /s /n /i "192.168.0.24" C:\xampp\apache\conf\*
```

---

## 8. Recomendación: usar un nombre en vez de la IP

Para no repetir todo esto en cada cambio de red, genera el certificado para un **nombre**
fijo en lugar de la IP:
```
mkcert dulymaz.local localhost 127.0.0.1
```
Usa siempre `https://dulymaz.local` (en `vite.config.js`, `.env` y `httpd-ssl.conf`) y añade
una línea en el fichero `hosts` (`C:\Windows\System32\drivers\etc\hosts`) de cada equipo
que lo consulte:
```
192.168.1.24   dulymaz.local
```
Así, aunque cambie la IP, el certificado **no se rompe**: solo se actualiza esa línea del `hosts`.
