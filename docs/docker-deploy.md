# Despliegue manual con Docker — guía paso a paso

Esta guía documenta cómo desplegar el proyecto **a mano** en un servidor, usando Docker y Docker Compose, sin pipeline de por medio. Es la base para poder automatizar después con Bitbucket Pipelines (ver [`ci-cd.md`](ci-cd.md)) — antes de automatizar un paso conviene poder hacerlo bien a mano y entender qué hace cada comando.

No cubre dominio/TLS/reverse proxy delante del puerto expuesto, ni backups — son decisiones pendientes, anotadas al final.

## Qué debe tener instalado el servidor

Todo el código de la aplicación corre **dentro de contenedores** — PHP, las extensiones, Composer y Postgres viven en las imágenes. El servidor host solo necesita:

- **Docker Engine** y el plugin **Docker Compose** (`docker compose`, con espacio — no el viejo `docker-compose` standalone). Verifica con:
  ```bash
  docker --version
  docker compose version
  ```
- **Git**, para clonar y actualizar el repositorio.

No hace falta instalar PHP, Composer ni Node en el servidor — ni siquiera para compilar los assets del frontend (ver paso 4, se hace con un contenedor Node desechable).

## Paso a paso

### 1. Clonar el repositorio

```bash
git clone https://bitbucket.org/tequia/cafe-del-tiempo.git
cd cafe-del-tiempo
```

(Esto es justo lo que ya hiciste — el repo queda en `~/cafe-del-tiempo` sobre `main`.)

### 2. Crear el archivo de entorno `.env`

`docker-compose.yml` lee `.env` (Docker Compose lo carga solo, sin necesidad de flags), que no está versionado. La plantilla se llama `.env.docker.example` — cópiala como `.env`:

```bash
cp .env.docker.example .env
```

Edita `.env` (`nano .env`) y ajusta al menos:

| Variable | Qué poner |
|---|---|
| `APP_URL` | La URL/IP pública real donde se va a acceder, ej. `http://TU_IP:9000` |
| `DB_PASSWORD` | Una contraseña fuerte — queda vacía en el ejemplo, Postgres no arranca sano sin ella |
| `APP_KEY` | Se genera en el paso 3, déjala vacía por ahora |

El resto de valores del `.env.docker.example` (nombres de conexión, `DB_HOST=postgres`, colas por base de datos, etc.) ya están pensados para este `docker-compose.yml` — no los cambies salvo que sepas por qué.

### 3. Generar el `APP_KEY`

Laravel necesita una `APP_KEY` de 32 bytes en base64. La forma más simple **sin depender de que el contenedor pueda escribir en el archivo montado** (el contenedor corre como `www-data` y el `.env` es del host, así que `php artisan key:generate` de adentro puede fallar por permisos) es generarla con `openssl` y pegarla a mano:

```bash
echo "base64:$(openssl rand -base64 32)"
```

Copia el valor que imprime y ponlo en `.env`:

```
APP_KEY=base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=
```

### 4. Compilar los assets del frontend (Vite)

`public/build/` está en `.gitignore` — no viaja con el `git clone`. El `Dockerfile` de este proyecto **no compila los assets**, espera encontrarlos ya generados en `public/build/` antes de construir la imagen (los copia tal cual con `COPY . .`). Si te saltas este paso, la app arranca pero cualquier página tira `ViteException: Unable to locate file in Vite manifest`.

Como el servidor no tiene Node instalado, se compila con un contenedor Node desechable, montando el proyecto:

```bash
docker run --rm -v "$PWD":/app -w /app node:22 sh -c "npm ci && npm run build"
```

Esto deja `public/build/` listo en el host, con el dueño de los archivos como `root` (por correr dentro del contenedor) — no afecta el build de la imagen, solo tenlo presente si luego quieres borrar esos archivos a mano (`sudo rm -rf public/build` si hace falta).

### 5. Construir las imágenes

```bash
docker compose build
```

Como el archivo se llama `.env`, Docker Compose lo toma solo — no hace falta pasar `--env-file` en ningún comando de aquí en adelante.

### 6. Levantar los servicios

```bash
docker compose up -d
```

Esto levanta 5 servicios (ver `docker-compose.yml`): `postgres`, `app` (PHP-FPM), `nginx` (expone el puerto `9000`), `queue` (worker de colas) y `scheduler` (`schedule:work`). `app`, `queue` y `scheduler` esperan a que Postgres esté sano (`healthcheck`) antes de arrancar.

Verifica que todo quedó arriba:

```bash
docker compose ps
```

Y revisa el log del contenedor `app` — su `entrypoint.sh` espera a Postgres y corre `php artisan optimize` (cachea config/rutas/vistas) antes de arrancar PHP-FPM:

```bash
docker compose logs -f app
```

Deberías ver `PostgreSQL disponible.` y `Optimizando Laravel...` sin errores.

### 7. Correr las migraciones

El `entrypoint.sh` **no** corre migraciones automáticamente (a propósito — correrlas solas en cada arranque es riesgoso). Hazlo a mano, la primera vez y cada vez que haya migraciones nuevas:

```bash
docker compose exec app php artisan migrate --force
```

(`--force` es necesario porque `APP_ENV=production` — Laravel pide confirmación explícita para migrar en producción, y esta flag la da sin prompt interactivo.)

### 8. Crear un usuario para entrar al panel

El panel de administración (Filament) vive en `/app` (definido en `AppPanelProvider`). Crea tu primer usuario:

```bash
docker compose exec app php artisan make:filament-user
```

Sigue el prompt (nombre, email, contraseña) y entra en `http://TU_IP:9000/app`.

Si el firewall del servidor bloquea el puerto por defecto, ábrelo (ejemplo con `ufw`):

```bash
sudo ufw allow 9000/tcp
```

## Actualizar a una versión nueva (deploy siguiente)

Una vez que el stack ya está arriba, actualizar el código es repetir el mismo patrón sin recrear nada desde cero:

```bash
cd ~/cafe-del-tiempo
git pull origin main
docker run --rm -v "$PWD":/app -w /app node:22 sh -c "npm ci && npm run build"
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

Este es, a mano, el mismo flujo que después va a ejecutar el pipeline de CD (ver [`ci-cd.md`](ci-cd.md)) — la diferencia es que ahí no hace falta el paso de Node porque el `bitbucket-pipelines.yml` sube los assets ya compilados como `artifact` antes de llegar al deploy. Aquí, a mano, hay que compilarlos en el servidor porque nadie más lo hizo antes.

## Comandos útiles del día a día

| Qué quieres hacer | Comando |
|---|---|
| Ver logs de un servicio | `docker compose logs -f app` (o `nginx`, `queue`, `scheduler`, `postgres`) |
| Entrar a una shell del contenedor `app` | `docker compose exec app sh` |
| Correr cualquier comando Artisan | `docker compose exec app php artisan <comando>` |
| Reiniciar un servicio sin reconstruir | `docker compose restart app` |
| Parar todo | `docker compose down` |
| Parar todo y borrar volúmenes (⚠️ borra la base de datos) | `docker compose down -v` |

## Problemas comunes

- **`ViteException: Unable to locate file in Vite manifest`**: te saltaste el paso 4, o lo corriste y luego reconstruiste la imagen sin volver a compilar. `public/build/` debe existir *antes* de `docker compose build`.
- **El contenedor `postgres` nunca queda "healthy"** y `app`/`queue`/`scheduler` se quedan esperando: revisa que `DB_PASSWORD` en `.env` no esté vacío — el `healthcheck` corre `pg_isready` con esas credenciales.
- **`php artisan key:generate` falla con permiso denegado dentro del contenedor**: es el problema de permisos mencionado en el paso 3 (el archivo `.env` del host no es escribible por `www-data` dentro del contenedor). Usa el método de `openssl` en su lugar.
- **El `.env` de Docker se pisa con el de desarrollo local (o viceversa)**: si alguna vez corres `php artisan serve` en el mismo checkout donde también usas Docker, van a pelear por el mismo `.env` (SQLite vs Postgres). Mantenlos en checkouts/carpetas separadas — esto no debería pasar en el servidor de producción, solo es un riesgo si trabajas en el mismo repo en tu máquina local.
- **Puerto `9000` ya en uso** en el servidor: cambia el mapeo de puertos del servicio `nginx` en `docker-compose.yml` (ej. `"8080:80"`) — pero recuerda que eso es un cambio de infraestructura, no de código de la app.

## Qué queda pendiente (fuera de esta guía)

1. **Reverse proxy + dominio + TLS** delante del puerto `9000` (Nginx/Caddy en el host, o Traefik) — hoy se accede directo por IP:puerto en HTTP plano.
2. **Backups de Postgres y del volumen `app_storage`** — no cubierto aquí ni en `vision.md` todavía.
3. **Automatizar todo esto** con Bitbucket Pipelines una vez el flujo manual esté probado — ver [`ci-cd.md`](ci-cd.md).
