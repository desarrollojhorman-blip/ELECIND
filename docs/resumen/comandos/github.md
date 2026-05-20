# Guia GitHub (ELECIND)

## Ruta base desde la que se ejecuta todo

Trabajamos siempre desde la raiz del proyecto:

D:\xampp\htdocs\CLIENTES\ELECIND

Si no estas en esa carpeta, entra primero:

cd D:\xampp\htdocs\CLIENTES\ELECIND

---

## Situacion 1: Proyecto recien clonado (primera vez)

### Comandos (en orden)

git clone https://github.com/usuario/ELECIND.git
cd ELECIND
git config user.name "Tu Nombre"
git config user.email "tu.email@ejemplo.com"
git branch -a
git status

### Que hace cada comando

- git clone https://github.com/usuario/ELECIND.git
Descarga todo el repositorio desde GitHub (incluye historial, todas las ramas).

- cd ELECIND
Entra en la carpeta del proyecto.

- git config user.name "Tu Nombre"
Configura tu nombre para commits (local al proyecto).

- git config user.email "tu.email@ejemplo.com"
Configura tu email para commits (local al proyecto).

- git branch -a
Muestra todas las ramas disponibles (local y remoto).

- git status
Verifica el estado actual (rama actual, archivos sin stagear).

Nota: si necesitas config global (en toda tu PC):
git config --global user.name "Tu Nombre"
git config --global user.email "tu.email@ejemplo.com"

---

## Situacion 2: Voy a empezar a trabajar en mi rama

### Comandos (en orden)

git pull origin main
git checkout -b mi-rama
git branch
git status

### Que hace cada comando

- git pull origin main
Descarga los cambios mas recientes de la rama main desde GitHub.

- git checkout -b mi-rama
Crea una rama nueva llamada "mi-rama" basada en main y te cambia a ella automaticamente.
Cambio: crear rama nueva con prefijo del tipo de trabajo (ej: feature/nueva-funcionalidad, fix/bug-login, etc.)

- git branch
Muestra todas tus ramas locales. La actual tendra un *.

- git status
Verifica en que rama estas y que archivos tienes sin stagear.

---

## Situacion 3: Quiero traer cambios de la rama admin a mi rama

### Comandos (en orden)

git status
git add .
git commit -m "descripcion del cambio"
git pull origin admin
git status

### Que hace cada comando

- git status
Verifica que no tengas cambios sin guardar en tu rama.

- git add .
Marca todos los cambios para ser commiteados (staging area).

- git commit -m "descripcion del cambio"
Guarda los cambios localmente con un mensaje claro.

- git pull origin admin
Descarga los cambios de la rama admin desde GitHub e intenta mergearlos en tu rama actual.

- git status
Verifica si hay conflictos o si el merge fue limpio.

Importante: si hay conflictos:
1. Abre los archivos con conflictos (tendran <<<<<<, ======, >>>>>> markers)
2. Resuelve manualmente o decide que cambio mantener
3. git add . (para marcar como resuelto)
4. git commit -m "merge: resueltos conflictos con admin" (para completar el merge)

---

## Situacion 4: Quiero hacer cambios, commitear y enviarlos a mi rama en GitHub

### Comandos (en orden)

git status
git add .
git commit -m "feature: descripcion clara del cambio"
git push origin mi-rama
git log --oneline -5

### Que hace cada comando

- git status
Muestra que archivos han cambiado desde el ultimo commit.

- git add .
Marca todos los cambios para commitear.

- git commit -m "mensaje descriptivo"
Guarda los cambios con un mensaje. Usa formato: tipo: descripcion (ej: feature: , fix: , docs: , etc.)

- git push origin mi-rama
Sube tu rama y los commits a GitHub.

- git log --oneline -5
Muestra los ultimos 5 commits para verificar que se grabaron bien.

---

## Situacion 5: Quiero descargar cambios de GitHub sin hacer cambios locales

### Comandos (en orden)

git fetch origin
git status
git log --oneline -5 origin/main

### Que hace cada comando

- git fetch origin
Descarga referencias de ramas remotas sin mergear ni modificar tu rama local.
Es seguro, solo observa que hay nuevo en GitHub.

- git status
Muestra si tu rama esta detras o adelante de la remota.

- git log --oneline -5 origin/main
Muestra los ultimos commits de main en GitHub sin cambiar tu ubicacion.

---

## Situacion 6: Acabo de hacer pull y hay conflictos

### Comandos (en orden)

git status
(edita manualmente los archivos con conflictos)
git add .
git commit -m "merge: resueltos conflictos"
git push origin mi-rama

### Que hace cada comando

- git status
Muestra que archivos tienen conflictos (en rojo, con estado "both modified").

- (edita manualmente)
Abre cada archivo conflictivo y elimina los marcadores:
<<<<<<< HEAD (tu rama)
tu contenido
=======
contenido de la otra rama
>>>>>>> nombre-rama

Elige que quedarse: tu version, su version, o una combinacion.

- git add .
Marca los conflictos como resueltos.

- git commit -m "merge: resueltos conflictos"
Completa el merge con un mensaje claro.

- git push origin mi-rama
Sube la resolucion a GitHub.

---

## Situacion 7: Quiero actualizar mi rama local con main remota

### Comandos (en orden)

git fetch origin
git rebase origin/main
(si hay conflictos, resuelvelos)
git push origin mi-rama --force-with-lease

### Que hace cada comando

- git fetch origin
Descarga referencias desde GitHub.

- git rebase origin/main
Re-aplica tus commits sobre la version mas reciente de main.
Esto mantiene el historial mas limpio que un merge.

- (si hay conflictos)
Resuelve archivos, luego:
git add .
git rebase --continue

- git push origin mi-rama --force-with-lease
Sube tu rama rebasada a GitHub.
--force-with-lease es mas seguro que --force (verifica cambios remotos).

Nota: usar rebase solo en ramas de trabajo personal, no en main.

---

## Situacion 8: Quiero sincronizarme con main antes de hacer merge a produccion

### Comandos (en orden)

git checkout main
git pull origin main
git checkout mi-rama
git merge main
(resuelve conflictos si existen)
git push origin mi-rama
git checkout main
git merge mi-rama
git push origin main

### Que hace cada comando

- git checkout main
Cambia a la rama principal.

- git pull origin main
Descarga los cambios mas recientes de main.

- git checkout mi-rama
Vuelve a tu rama de trabajo.

- git merge main
Mergea main hacia tu rama (trae cambios de otros).

- (resuelve conflictos si existen)
Ver Situacion 6.

- git push origin mi-rama
Actualiza tu rama en GitHub.

- git checkout main
Cambia a main.

- git merge mi-rama
Mergea tu rama hacia main (prepara para produccion).

- git push origin main
Sube el merge a GitHub.

---

## Situacion 9: Quiero deshacer cambios locales sin commiteados

### Comandos

git status
git restore .
git status

### Que hace cada comando

- git status
Muestra archivos modificados sin stagear.

- git restore .
Descarta todos los cambios sin stagear (los revierte al ultimo commit).

- git status
Confirma que todo esta limpio.

Importante: esto DESHACE cambios irreversiblemente. Asegurate de que realmente quieras descartar.

---

## Situacion 10: Quiero deshacer el ultimo commit que ya hice push

### Comandos (en orden)

git revert HEAD
git push origin mi-rama

### Que hace cada comando

- git revert HEAD
Crea un commit NUEVO que revierte los cambios del ultimo commit.
Es seguro porque no borra historial, solo desace cambios.

- git push origin mi-rama
Sube el commit de revert a GitHub.

Alternativa si aun no hiciste push (solo commits locales):
git reset --soft HEAD~1
(para volver atras sin perder cambios en archivos)

---

## Situacion 11: Necesito ver el historial de commits

### Comandos

git log --oneline -10
git log --graph --oneline --all -15
git log --author="Tu Nombre" --oneline

### Que hace cada comando

- git log --oneline -10
Muestra ultimos 10 commits en formato corto.

- git log --graph --oneline --all -15
Muestra ultimos 15 commits en formato visual con ramas.

- git log --author="Tu Nombre" --oneline
Muestra commits de un autor especifico.

---

## Situacion 12: Quiero limpiar ramas locales que ya no necesito

### Comandos (en orden)

git branch -a
git branch -d mi-rama-vieja
git branch -D mi-rama-vieja-fuerza
git push origin --delete mi-rama-remota-vieja
git branch -a

### Que hace cada comando

- git branch -a
Muestra todas las ramas (local y remoto).

- git branch -d mi-rama-vieja
Borra la rama local si ya fue mergeada (seguro).

- git branch -D mi-rama-vieja-fuerza
Fuerza borrar la rama local sin importar si fue mergeada (cuidado).

- git push origin --delete mi-rama-remota-vieja
Borra la rama del repositorio remoto (GitHub).

- git branch -a
Confirma que las ramas fueron borradas.

---

## Flujo recomendado por tarea

### De inicio a fin en una tarea

1. git pull origin main                           # Trae cambios de main
2. git checkout -b feature/nombre-tarea           # Crea tu rama
3. (hace cambios en archivos)
4. git add .                                      # Stagea cambios
5. git commit -m "feature: descripcion"           # Commitea
6. git push origin feature/nombre-tarea           # Sube a GitHub
7. (en GitHub: abre Pull Request)
8. (otros revisan y aprueban)
9. git checkout main                              # Vuelve a main
10. git pull origin main                          # Sincroniza main
11. git merge feature/nombre-tarea                # Mergea tu rama a main
12. git push origin main                          # Sube main
13. git branch -d feature/nombre-tarea            # Limpia tu rama local
14. git push origin --delete feature/nombre-tarea # Limpia rama remota

---

## Mini regla rapida

- Antes de empezar trabajo: git pull origin main
- Despues de cambios: git add . , git commit -m "..." , git push origin tu-rama
- Para traer cambios de otro: git pull origin nombre-rama
- Conflictos: editar archivos, git add . , git commit
- Antes de mergear a main: asegurate que main este actualizada (pull origin main)
- Siempre usa mensajes descriptivos en commits (tipo: descripcion)

---

## Convenciones de ramas

Usa estos prefijos al crear ramas:

- feature/nombre: para nuevas funcionalidades
- fix/nombre: para correcciones de bugs
- hotfix/nombre: para urgencias en produccion
- docs/nombre: para cambios en documentacion
- refactor/nombre: para reorganizacion de codigo
- test/nombre: para pruebas nuevas

Ejemplo:
git checkout -b feature/modulo-nuevos-usuarios
git checkout -b fix/bug-reporte-albaranes
git checkout -b docs/actualizar-readme

---

## Glosario rapido

- **main**: rama principal (produccion).
- **origin**: nombre del repositorio remoto (GitHub).
- **HEAD**: tu ubicacion actual en el historial.
- **stage/staging**: area donde preparas cambios antes de commitear.
- **merge**: combinar cambios de una rama en otra.
- **rebase**: re-aplicar commits sobre otra rama (historial mas limpio).
- **push**: enviar commits a GitHub.
- **pull**: descargar y mergear cambios desde GitHub.
- **fetch**: solo descargar cambios de GitHub (sin mergear).
- **commit**: guardar cambios localmente con mensaje.
