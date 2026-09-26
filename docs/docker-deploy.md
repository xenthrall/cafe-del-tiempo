# Despliegue manual con Docker — guía paso a paso

Esta guía documenta cómo desplegar el proyecto **a mano** en un servidor con Docker y Docker Compose. Es el flujo con el que se despliega cafe.tequia.dev; la automatización con Bitbucket Pipelines está en [`ci-cd.md`](ci-cd.md).

Cubre también HTTPS detrás de un proxy inverso y los respaldos. La versión resumida para usuarios está en la guía pública: [cafe.tequia.dev/docs/instalacion](https://cafe.tequia.dev/docs/instalacion).

## Qué debe tener instalado el servidor

Todo el código de la aplicación corre **dentro de contenedores** — PHP, las extensiones, Composer y Postgres viven en las imágenes. El servidor host solo necesita:

- **Docker Engine** y el plugin **Docker Compose** (`docker compose`, con espacio — no el viejo `docker-compose` standalone). Verifica con:
  ```bash
  docker --version
  docker compose version
  ```
- **Git**, para clonar y actualizar el repositorio.

No hace falta instalar PHP, Composer ni Node en el servidor. Los assets del frontend (`public/build/`) tampoco se compilan aquí — se compilan en tu máquina y se comitean al repo (ver paso 4), así que el servidor ni siquiera necesita un contenedor Node desechable.

## Paso a paso

### 1. Clonar el repositorio

```bash
git clone https://github.com/xenthrall/cafe-del-tiempo.git
cd cafe-del-tiempo
```

El repo queda en `~/cafe-del-tiempo` sobre `main`.

### 2. Crear el archivo de entorno `.env`

`docker-compose.yml` lee `.env` (Docker Compose lo carga solo, sin necesidad de flags), que no está versionado. La plantilla se llama `.env.docker.example` — cópiala como `.env`:

```bash
cp .env.docker.example .env
```

Edita `.env` (`nano .env`) y ajusta al menos:

| Variable | Qué poner |
|---|---|
| `APP_URL` | La URL pública real, con el esquema correcto: `http://TU_IP:9000` si accedes directo, o `https://tu-dominio` si hay un reverse proxy/túnel (Cloudflare Tunnel, nginx, etc.) delante que sirve HTTPS |
| `DB_PASSWORD` | Una contraseña fuerte — queda vacía en el ejemplo, Postgres no arranca sano sin ella |
| `APP_KEY` | Se genera en el paso 3, déjala vacía por ahora |
| `APP_INSTANCE` | `self-hosted` (por defecto, registro cerrado) o `hosted` (registro público, como cafe.tequia.dev) |
| `R2_PRIVATE_*` | Credenciales del bucket de Cloudflare R2 para los respaldos (ver [Respaldos](#respaldos)) |
| `SESSION_SECURE_COOKIE` | `true` si `APP_URL` es `https://...` (evita que la cookie de sesión viaje sin el flag `Secure`) |

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

### 4. Verificar que `public/build/` esté presente

El `Dockerfile` de este proyecto **no compila los assets**, espera encontrarlos ya generados en `public/build/` antes de construir la imagen (los copia tal cual con `COPY . .`). Si no están, la app arranca pero cualquier página tira `ViteException: Unable to locate file in Vite manifest`.

`public/build/` **se compila en tu máquina de desarrollo y se comitea al repo** (`npm run build && git add public/build`) — no en el servidor. Como ya viaja con el `git clone`/`git pull`, en este paso no hay nada que ejecutar: solo confirma que la carpeta existe.

```bash
ls public/build/manifest.json
```

Si no aparece, es que el commit que trajiste no incluye los assets compilados — vuelve a la máquina donde desarrollas, corre `npm run build`, comitea `public/build/` y haz `git pull` de nuevo en el servidor.

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

El panel personal (Filament) vive en `/app` y el de administración de la instancia en `/system`. Crea tu primer usuario como administrador para tener acceso a ambos:

```bash
docker compose exec app php artisan user:create --admin
```

Sigue el prompt (nombre, email, contraseña) y entra en `http://TU_IP:9000/app`. Sin `--admin` el usuario solo entra a `/app`; `--name` y `--email` evitan escribirlos en el prompt (la contraseña siempre se pide de forma interactiva).

Si el firewall del servidor bloquea el puerto por defecto, ábrelo (ejemplo con `ufw`):

```bash
sudo ufw allow 9000/tcp
```

## Si expones el sitio detrás de un reverse proxy o túnel (HTTPS)

Si en vez de entrar directo por `http://TU_IP:9000` pones algo delante que sirve HTTPS al navegador (Cloudflare Tunnel, nginx, otro load balancer) apuntando al puerto `9000`, la petición le sigue llegando a Laravel como HTTP plano por dentro — el proxy termina el TLS, y por defecto Laravel no sabe que el tráfico original era seguro. Esto se nota porque el navegador bloquea peticiones de Livewire (u otros assets) por **contenido mixto**: la página se sirve por HTTPS pero Laravel genera URLs con `http://`.

Esto ya está resuelto en el código (`bootstrap/app.php` confía en el proxy vía `$middleware->trustProxies(at: '*')`, para que Laravel lea la cabecera `X-Forwarded-Proto` que reenvía el proxy y sepa que la petición original fue HTTPS), pero para que funcione de punta a punta también necesitas, en el `.env` del servidor:

1. `APP_URL=https://tu-dominio` (el dominio público real, no `http://IP:9000`).
2. `SESSION_SECURE_COOKIE=true`.

Y reiniciar los contenedores para que tomen el `.env` nuevo:

```bash
docker compose up -d
```

`trustProxies(at: '*')` confía en **cualquier** IP como proxy — válido aquí porque el puerto de `app` (PHP-FPM) no se publica al host, solo `nginx` lo hace, así que lo único que puede hablarle a `app` es el propio `nginx` del mismo `docker-compose.yml` (su IP en la red interna de Docker cambia entre despliegues, por eso no se puede fijar una IP concreta). Si el día de mañana expones `app` directamente a internet sin `nginx`/proxy de por medio, esta confianza total dejaría de ser segura y habría que restringirla a una IP fija.

## Respaldos

El servicio `scheduler` ejecuta dos tareas diarias, definidas en `app-modules/system/routes/console.php`:

| Hora | Comando | Qué hace |
|---|---|---|
| 01:30 | `backup:clean` | Borra los respaldos viejos según la retención de `config/backup.php`. |
| 02:00 | `backup:run --only-db` | Genera un `.zip` con el dump de PostgreSQL y lo sube al disco `r2_private`. |

Solo se respalda la base de datos: el código está en Git, y un respaldo de archivos incluiría el `.env` con las credenciales. Por eso conviene guardar una copia del `.env` fuera del servidor.

La retención (`config/backup.php`) conserva todos los respaldos de los últimos 7 días, uno diario durante 16 días, uno semanal durante 8 semanas, uno mensual durante 4 meses y uno anual durante 2 años. Si el total supera 5000 MB, se borran los más antiguos.

Comandos útiles:

```bash
docker compose exec app php artisan backup:run --only-db   # respaldo manual
docker compose exec app php artisan backup:list            # estado de los respaldos
```

Para restaurar, descarga el `.zip` desde R2, extrae el `.sql` de la carpeta `db-dumps/` y cárgalo en el contenedor de Postgres:

```bash
docker compose exec -T postgres psql -U "$DB_USERNAME" -d "$DB_DATABASE" < db-dumps/postgresql-cafe_del_tiempo.sql
```

Haz una restauración de prueba en un entorno aparte de vez en cuando: un respaldo que nunca se restauró no está comprobado.

## Actualizar a una versión nueva (deploy siguiente)

Una vez que el stack ya está arriba, actualizar el código es repetir el mismo patrón sin recrear nada desde cero:

```bash
cd ~/cafe-del-tiempo
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
```

`git pull` trae el código **y** el `public/build/` ya compilado y comiteado — no hay nada que compilar en el servidor. Es el mismo flujo que ejecuta el deploy del pipeline de Bitbucket (ver [`ci-cd.md`](ci-cd.md)).

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

- **`ViteException: Unable to locate file in Vite manifest`**: `public/build/` no está presente en el checkout — significa que el commit que trajiste no lo incluye. Compílalo en tu máquina (`npm run build`), comitéalo, y vuelve a hacer `git pull` en el servidor antes de reconstruir.
- **El contenedor `postgres` nunca queda "healthy"** y `app`/`queue`/`scheduler` se quedan esperando: revisa que `DB_PASSWORD` en `.env` no esté vacío — el `healthcheck` corre `pg_isready` con esas credenciales.
- **`php artisan key:generate` falla con permiso denegado dentro del contenedor**: es el problema de permisos mencionado en el paso 3 (el archivo `.env` del host no es escribible por `www-data` dentro del contenedor). Usa el método de `openssl` en su lugar.
- **El `.env` de Docker se pisa con el de desarrollo local (o viceversa)**: si alguna vez corres `php artisan serve` en el mismo checkout donde también usas Docker, van a pelear por el mismo `.env` (SQLite vs Postgres). Mantenlos en checkouts/carpetas separadas — esto no debería pasar en el servidor de producción, solo es un riesgo si trabajas en el mismo repo en tu máquina local.
- **Puerto `9000` ya en uso** en el servidor: cambia el mapeo de puertos del servicio `nginx` en `docker-compose.yml` (ej. `"8080:80"`) — pero recuerda que eso es un cambio de infraestructura, no de código de la app.
- **`GET /livewire-XXXXXXXX/livewire.min.js` da 404**: el bloque de nginx que cachea `.js`/`.css`/etc. (`docker/nginx/default.conf`) intercepta esa ruta antes que Laravel porque *parece* un archivo estático por la extensión, pero es una ruta dinámica que registra Livewire (no existe como archivo en `public/`). Ya está resuelto en el `default.conf` del repo (cae a `index.php` si el archivo no existe en vez de devolver 404 directo) — si lo ves en un servidor viejo, actualiza ese archivo con `git pull` y `docker compose restart nginx` (no hace falta reconstruir, es un volumen montado).

## Qué queda pendiente

1. **Volumen `app_storage`**: no se respalda. Hoy no guarda archivos de usuarios (sesiones y colas viven en la base de datos), pero habría que incluirlo si algún módulo empieza a guardar archivos.
2. **Pruebas de restauración**: no están automatizadas; se hacen a mano.
