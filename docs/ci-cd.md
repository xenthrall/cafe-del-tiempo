# CI/CD con Bitbucket Pipelines — guía y ejemplo

Este documento explica cómo aplicar CI/CD a este proyecto usando Bitbucket Pipelines, ya que el repositorio remoto vive en Bitbucket. Es una guía práctica, no una decisión cerrada — ajusta lo que no te sirva. No cubre la estrategia de backup/recuperación de datos de la bóveda (eso sigue pendiente en `vision.md`); esto es solo sobre automatizar pruebas y despliegue del código.

## CI vs CD, en corto

- **CI (Integración Continua)**: cada vez que subes código, un robot corre tus tests, tu linter y compila tus assets automáticamente. Si algo falla, te enteras en minutos, no cuando ya está en producción.
- **CD (Entrega/Despliegue Continuo)**: cuando el código en `main` pasa CI, otro paso automático se conecta a tu servidor y reconstruye/levanta el contenedor con el código nuevo, sin que tengas que hacerlo a mano por SSH.

Para este proyecto, el flujo elegido es:

1. Trabajas sobre la rama **`develop`** (directo o con ramas de feature que mergeas ahí).
2. Cuando `develop` está listo, abres una **pull request `develop` → `main`**. Esa PR dispara CI (tests + estilo + build de assets) — si algo falla, no deberías mergear.
3. Al hacer **merge a `main`**, se dispara un segundo pipeline: primero corre CI de nuevo (sobre el estado final de `main`), y si pasa, pasa automáticamente a **CD**.
4. CD no usa un registry de imágenes: el pipeline se conecta **por SSH a tu servidor**, entra a la carpeta del proyecto, hace `git pull origin main` y corre `docker compose up -d --build`. La imagen se construye directamente en el servidor, no se sube ni se descarga de ningún lado.

Este enfoque es más simple de entender (un componente menos, el registry) a cambio de dos cosas a tener en cuenta: el build consume recursos del propio servidor de producción mientras corre, y "volver atrás" ya no es "desplegar la imagen anterior" sino hacer `git checkout`/`revert` a un commit anterior y reconstruir. Para un proyecto personal donde el objetivo es aprender el flujo de CI/CD, es una elección razonable.

Dos ramas (`main`/`develop`) más PRs es el modelo mínimo para practicar esto: te obliga a pasar por PR (donde ves el resultado de CI antes de mergear) en vez de empujar directo a producción, sin la complejidad de GitFlow completo (sin ramas `release`/`hotfix`).

## Cómo funciona Bitbucket Pipelines

- Vive en un archivo **`bitbucket-pipelines.yml`** en la raíz del repo. Bitbucket lo detecta solo.
- Cada "pipeline" corre dentro de un contenedor Docker (tú eliges la imagen base, ej. `php:8.3`).
- Se activa por: push a una rama (`branches`), pull request (`pull-requests`), tag, o manualmente (`custom`).
- Un pipeline tiene **steps**; cada step es un contenedor limpio (a menos que uses `services` o compartas `artifacts` entre steps).
- **Caches**: evitan reinstalar `vendor/`/`node_modules/` en cada corrida si no cambiaron.
- **Artifacts**: pasan archivos generados en un step (ej. `public/build/`) al siguiente step del mismo pipeline.
- **Variables**: se configuran en Bitbucket (Repository settings → Repository variables, o Deployments → Environment variables), nunca en el YAML en texto plano. Las marcas como "Secured" no se muestran en los logs.
- **Deployments**: Bitbucket tiene el concepto de "environments" (ej. `production`) que puedes usar para exigir aprobación manual antes de desplegar, y para ver el historial de qué se desplegó y cuándo.
- **Branch permissions / merge checks**: en *Repository settings → Branch permissions* puedes exigir que `main` solo reciba cambios vía pull request (no push directo) y que la PR tenga "passing build" antes de poder mergear. Esto es lo que hace cumplir en la práctica el flujo "PR develop → main + CI en verde obligatorio" que quieres.

Bitbucket cobra por minutos de build (hay un plan gratuito con minutos limitados al mes). Para un proyecto personal esto normalmente alcanza sin problema.

## CI para este proyecto

Lo que ya tenemos para validar en cada push, todo corrible desde la CLI:

| Chequeo | Comando |
|---|---|
| Tests (Pest, SQLite en memoria) | `php artisan test --compact` |
| Estilo de código (Pint) | `vendor/bin/pint --test` |
| Build de assets (Vite + hash-wasm) | `npm run build` |

Un detalle propio de este proyecto: `tequia/app` y `tequia/vault` son **path repositories** (`app-modules/*`), no paquetes de Packagist — pero como en CI se clona el repo completo (a diferencia del build de Docker, que copia por capas), `composer install` los resuelve sin configuración extra.

### `bitbucket-pipelines.yml` de ejemplo

```yaml
image: php:8.3-cli

definitions:
  caches:
    composer: vendor
    npm: node_modules
  steps:
    - step: &tests
        name: Tests + estilo
        caches:
          - composer
        script:
          - apt-get update && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev libpq-dev > /dev/null
          - docker-php-ext-configure intl && docker-php-ext-install intl zip pdo_pgsql > /dev/null
          - curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
          - composer install --no-interaction --prefer-dist
          - cp .env.example .env
          - php artisan key:generate
          - vendor/bin/pint --test
          - php artisan test --compact
    - step: &build-assets
        name: Build de assets (Vite)
        image: node:22
        caches:
          - npm
        script:
          - npm ci
          - npm run build
        artifacts:
          - public/build/**

pipelines:
  pull-requests:
    '**':
      - step: *tests
      - step: *build-assets

  branches:
    main:
      - step: *tests
      - step: *build-assets
      - step:
          name: Desplegar a producción
          deployment: production
          script:
            - pipe: atlassian/ssh-run:0.10.0
              variables:
                SSH_USER: $DEPLOY_SSH_USER
                SERVER: $DEPLOY_SSH_HOST
                SSH_KEY: $DEPLOY_SSH_KEY
                COMMAND: |
                  cd /ruta/en/tu/servidor/cafe-del-tiempo &&
                  git pull origin main &&
                  docker compose up -d --build &&
                  docker compose exec -T app php artisan migrate --force
```

Notas sobre este ejemplo:

- La imagen `php:8.3-cli` no trae `intl`/`pdo_pgsql`, por eso se instalan en el step — igual que tuvimos que ajustar en el `Dockerfile`. Si esto se vuelve lento, puedes construir y publicar tu propia imagen base con esas extensiones ya compiladas, y usarla aquí en vez de `php:8.3-cli`.
- El step de tests corre contra **SQLite en memoria** (ya configurado en `phpunit.xml`), no contra Postgres — no hace falta levantar un servicio de base de datos para correr Pest, lo cual mantiene el pipeline simple y rápido.
- `pull-requests: '**'` corre CI en cualquier PR (incluida `develop → main`) antes de que puedas aprobarla — combinado con un branch permission que exija "passing build", Bitbucket no te deja mergear si esto falla.
- El pipeline de `branches: main` vuelve a correr CI sobre el commit de merge ya en `main` (no confía ciegamente en el resultado de la PR) y, si pasa, encadena el deploy sin registry: no hay build/push de imagen, el `docker compose up -d --build` de la última etapa construye la imagen directo en el servidor a partir del código recién bajado con `git pull`.
- El step de deploy no tiene `trigger: manual`, así que se ejecuta automático apenas termina CI — esto coincide con lo que pediste ("al mergear a main, se despliega solo"). Si más adelante quieres un botón de confirmación antes de tocar producción, basta con agregar `trigger: manual` a ese step; con `deployment: production` puesto, Bitbucket igual te deja ver el historial de qué se desplegó y cuándo.
- El pipe `atlassian/ssh-run` es uno de varios "pipes" oficiales de Bitbucket (bloques reutilizables) para tareas comunes como SSH, rsync, o deploy a un proveedor específico — evita reescribir ese código a mano.
- El servidor necesita poder hacer `git pull origin main` sin pedir contraseña interactiva: configúrale su propia clave SSH (o un deploy key de solo lectura del repo) por separado de la clave que usa el pipeline para conectarse *a* él — son dos llaves distintas con dos propósitos distintos.

## Variables y secretos que vas a necesitar configurar

En Bitbucket: **Deployments → production → Environment variables** (scoped solo a ese environment, ya que solo se usan en el step de deploy):

| Variable | Dónde | Para qué |
|---|---|---|
| `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`, `DEPLOY_SSH_KEY` | Deployment (production) | Conectarse a tu servidor por SSH y ejecutar el `git pull` + `docker compose up -d --build` |

Como no hay registry, no necesitas credenciales de Docker Hub ni de ningún otro registry — un componente menos que asegurar.

El `APP_KEY`, las credenciales de Postgres, etc. **no viajan por el pipeline** — viven directamente en el `.env` de tu servidor (el mismo que ya armamos, gitignored, generado a partir de `.env.docker.example`), y el pipeline solo hace `git pull` + `docker compose up`, sin tocar esos secretos.

## Qué te falta decidir para que esto funcione de verdad

Este documento te deja el patrón, pero hay decisiones tuyas que no puedo tomar por ti:

1. **Que el servidor ya tenga Docker + Docker Compose instalados**, el repo clonado en una ruta fija, y una clave SSH propia con acceso de lectura al repo para poder hacer `git pull origin main` sin interacción.
2. **La clave SSH que usará el pipeline para *entrar* al servidor** (`DEPLOY_SSH_KEY`) — normalmente generas un par nuevo dedicado a esto, y agregas la pública a `~/.ssh/authorized_keys` del usuario de deploy en el servidor.
3. **Si quieres que el deploy sea automático o con aprobación manual** — el ejemplo de arriba lo deja automático (como pediste), pero es un cambio de una línea (`trigger: manual`) si luego prefieres un botón de confirmación.
4. **Si quieres correr CI también en pushes directos a `develop`** (sin PR) para tener feedback más temprano — se agregaría un bloque `branches: develop:` con solo el step de tests, sin deploy.
5. **Cómo rotas el `APP_KEY`/credenciales de Postgres en el servidor** la primera vez — eso es manual, fuera del pipeline (ver [`docker-deploy.md`](docker-deploy.md) para el paso a paso, incluyendo por qué se genera con `openssl` en vez de `php artisan key:generate` y se guarda en `.env` del servidor).

Cuando tengas esas respuestas (sobre todo la 1 y la 2, que son las que bloquean poder probar el pipeline end-to-end), puedo ayudarte a crear el `bitbucket-pipelines.yml` real (no solo el de ejemplo de esta guía), configurar el branch permission de `main`, y dejarlo commiteado.
