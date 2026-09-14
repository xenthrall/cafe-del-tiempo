# CI/CD con Bitbucket Pipelines — guía y ejemplo

Este documento explica cómo aplicar CI/CD a este proyecto usando Bitbucket Pipelines, ya que el repositorio remoto vive en Bitbucket. Es una guía práctica, no una decisión cerrada — ajusta lo que no te sirva. No cubre la estrategia de backup/recuperación de datos de la bóveda (eso sigue pendiente en `vision.md`); esto es solo sobre automatizar pruebas y despliegue del código.

## CI vs CD, en corto

- **CI (Integración Continua)**: cada vez que subes código, un robot corre tus tests, tu linter y compila tus assets automáticamente. Si algo falla, te enteras en minutos, no cuando ya está en producción.
- **CD (Entrega/Despliegue Continuo)**: cuando el código en `main` pasa CI, otro paso automático construye la imagen Docker y la despliega a tu servidor, sin que tengas que hacerlo a mano por SSH.

Para este proyecto: **CI en cada push/PR** (tests + estilo + build de assets) y **CD manual-con-un-clic hacia producción** (construir imagen → subirla a un registry → desplegar por SSH a tu servidor). "Manual-con-un-clic" porque tu proyecto es de un solo usuario y self-hosted — no hay necesidad de auto-desplegar cada commit a producción sin que tú lo confirmes.

## Cómo funciona Bitbucket Pipelines

- Vive en un archivo **`bitbucket-pipelines.yml`** en la raíz del repo. Bitbucket lo detecta solo.
- Cada "pipeline" corre dentro de un contenedor Docker (tú eliges la imagen base, ej. `php:8.3`).
- Se activa por: push a una rama (`branches`), pull request (`pull-requests`), tag, o manualmente (`custom`).
- Un pipeline tiene **steps**; cada step es un contenedor limpio (a menos que uses `services` o compartas `artifacts` entre steps).
- **Caches**: evitan reinstalar `vendor/`/`node_modules/` en cada corrida si no cambiaron.
- **Artifacts**: pasan archivos generados en un step (ej. `public/build/`) al siguiente step del mismo pipeline.
- **Variables**: se configuran en Bitbucket (Repository settings → Repository variables, o Deployments → Environment variables), nunca en el YAML en texto plano. Las marcas como "Secured" no se muestran en los logs.
- **Deployments**: Bitbucket tiene el concepto de "environments" (ej. `production`) que puedes usar para exigir aprobación manual antes de desplegar, y para ver el historial de qué se desplegó y cuándo.

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
          name: Build y push de imagen Docker
          image: atlassian/default-image:4
          services:
            - docker
          script:
            - docker build -t $DOCKERHUB_USER/cafe-del-tiempo:$BITBUCKET_COMMIT -t $DOCKERHUB_USER/cafe-del-tiempo:latest .
            - echo "$DOCKERHUB_TOKEN" | docker login -u "$DOCKERHUB_USER" --password-stdin
            - docker push $DOCKERHUB_USER/cafe-del-tiempo:$BITBUCKET_COMMIT
            - docker push $DOCKERHUB_USER/cafe-del-tiempo:latest
      - step:
          name: Desplegar a producción
          deployment: production
          trigger: manual
          script:
            - pipe: atlassian/ssh-run:0.10.0
              variables:
                SSH_USER: $DEPLOY_SSH_USER
                SERVER: $DEPLOY_SSH_HOST
                SSH_KEY: $DEPLOY_SSH_KEY
                COMMAND: |
                  cd /ruta/en/tu/servidor/cafe-del-tiempo &&
                  git pull origin main &&
                  docker compose --env-file .env.docker pull app &&
                  docker compose --env-file .env.docker up -d --build &&
                  docker compose --env-file .env.docker exec -T app php artisan migrate --force
```

Notas sobre este ejemplo:

- La imagen `php:8.3-cli` no trae `intl`/`pdo_pgsql`, por eso se instalan en el step — igual que tuvimos que ajustar en el `Dockerfile`. Si esto se vuelve lento, puedes construir y publicar tu propia imagen base con esas extensiones ya compiladas, y usarla aquí en vez de `php:8.3-cli`.
- El step de tests corre contra **SQLite en memoria** (ya configurado en `phpunit.xml`), no contra Postgres — no hace falta levantar un servicio de base de datos para correr Pest, lo cual mantiene el pipeline simple y rápido.
- `pull-requests: '**'` corre CI en cualquier PR antes de aprobarlo — así detectas roturas antes de mezclar a `main`.
- El step de deploy usa `deployment: production` + `trigger: manual`: aparece en Bitbucket como un botón "Deploy" que tú presionas, no algo automático. Puedes agregar un environment `staging` con `trigger: automatic` si más adelante quieres un ambiente de pruebas que sí se actualice solo.
- El pipe `atlassian/ssh-run` es uno de varios "pipes" oficiales de Bitbucket (bloques reutilizables) para tareas comunes como SSH, rsync, o deploy a un proveedor específico — evita reescribir ese código a mano.
- Cambia `$DOCKERHUB_USER`/`$DOCKERHUB_TOKEN` por las credenciales del registry que uses (Docker Hub, GitHub Container Registry, un registry propio, etc.). No hay una elección "correcta" aquí — es la que ya tengas o prefieras.

## Variables y secretos que vas a necesitar configurar

En Bitbucket: **Repository settings → Repository variables** (para CI) y **Deployments → production → Environment variables** (para CD, scoped solo a ese environment):

| Variable | Dónde | Para qué |
|---|---|---|
| `DOCKERHUB_USER`, `DOCKERHUB_TOKEN` | Repository | Push de la imagen al registry |
| `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`, `DEPLOY_SSH_KEY` | Deployment (production) | Conectarse a tu servidor por SSH |

El `APP_KEY`, las credenciales de Postgres, etc. **no viajan por el pipeline** — viven directamente en el `.env.docker` de tu servidor (el mismo que ya armamos, gitignored), y el pipeline solo hace `git pull` + `docker compose up`, sin tocar esos secretos. Esto es justamente lo que ya dejamos separado al crear `.env.docker.example`.

## Qué te falta decidir para que esto funcione de verdad

Este documento te deja el patrón, pero hay decisiones tuyas que no puedo tomar por ti:

1. **Dónde vive el registry de imágenes** — Docker Hub (gratis para repos públicos, límite en privados), GitHub Container Registry, o uno propio.
2. **Dónde vive el servidor de producción** — una VPS tuya, y si ya tiene Docker instalado.
3. **Si quieres un ambiente de `staging`** antes de producción, o vas directo con aprobación manual.
4. **Cómo rotas el `APP_KEY`/credenciales de Postgres en el servidor** la primera vez — eso es manual, fuera del pipeline (generarlos una vez con `php artisan key:generate --show` y guardarlos en `.env.docker` del servidor).

Cuando tengas esas respuestas, puedo ayudarte a crear el `bitbucket-pipelines.yml` real (no solo el de ejemplo de esta guía) y dejarlo commiteado.
