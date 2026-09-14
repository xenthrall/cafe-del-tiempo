# CI/CD con Bitbucket Pipelines — guía y ejemplo

Este documento explica cómo aplicar CI/CD a este proyecto usando Bitbucket Pipelines, ya que el repositorio remoto vive en Bitbucket. Es una guía práctica, no una decisión cerrada — ajusta lo que no te sirva. No cubre la estrategia de backup/recuperación de datos de la bóveda (eso sigue pendiente en `vision.md`); esto es solo sobre automatizar pruebas y despliegue del código.

## CI vs CD, en corto

- **CI (Integración Continua)**: cada vez que subes código, un robot corre tus tests y tu linter automáticamente. Si algo falla, te enteras en minutos, no cuando ya está en producción.
- **CD (Entrega/Despliegue Continuo)**: cuando el código en `main` pasa CI, otro paso automático se conecta a tu servidor y reconstruye/levanta el contenedor con el código nuevo, sin que tengas que hacerlo a mano por SSH.

Para este proyecto, el flujo elegido es:

1. Trabajas sobre la rama **`develop`** (directo o con ramas de feature que mergeas ahí).
2. Cuando `develop` está listo, abres una **pull request `develop` → `main`**. Esa PR dispara CI (tests + estilo) — si algo falla, no deberías mergear.
3. Al hacer **merge a `main`**, se dispara un segundo pipeline: primero corre CI de nuevo (sobre el estado final de `main`), y si pasa, pasa automáticamente a **CD**.
4. CD no usa un registry de imágenes: el pipeline se conecta **por SSH a tu servidor**, entra a la carpeta del proyecto, hace `git pull origin main` y corre `docker compose up -d --build`. La imagen se construye directamente en el servidor, no se sube ni se descarga de ningún lado.

Este enfoque es más simple de entender (un componente menos, el registry) a cambio de dos cosas a tener en cuenta: el build consume recursos del propio servidor de producción mientras corre, y "volver atrás" ya no es "desplegar la imagen anterior" sino hacer `git checkout`/`revert` a un commit anterior y reconstruir. Para un proyecto personal donde el objetivo es aprender el flujo de CI/CD, es una elección razonable.

**Cambio de estrategia sobre assets**: en vez de que el pipeline compile `public/build/` (con Node) y lo suba al servidor, ahora **tú compilas localmente y comiteas `public/build/` directo al repo** (`git add public/build -f`, ya que la carpeta seguía en `.gitignore` — se la quité, ver más abajo). Esto saca a Node por completo tanto del servidor como del pipeline: el `Dockerfile` ya esperaba encontrar `public/build/` listo antes de construir la imagen (ver [`docker-deploy.md`](docker-deploy.md)), así que el servidor solo necesita `git pull` + `docker compose up -d --build`.

Habíamos agregado un step de CI que corría `npm run build` solo para *verificar* (con `git diff`) que lo comiteado coincidía con el fuente, pero lo quitamos: ese step necesitaba también `vendor/` (Composer) para resolver los estilos de Filament, y como corría en una imagen Node aparte sin `composer install`, fallaba siempre — arreglarlo bien (compartir `vendor/` entre steps con `artifacts`, o instalar PHP+Composer en la imagen Node) consume minutos gratis de Bitbucket que por ahora preferimos no gastar. El riesgo que esto deja abierto — olvidar recompilar `public/build/` antes de comitear un cambio de frontend — queda sobre ti por ahora; si más adelante quieres blindarlo, se puede retomar como mejora del pipeline.

Dos ramas (`main`/`develop`) más PRs es el modelo mínimo para practicar esto: te obliga a pasar por PR (donde ves el resultado de CI antes de mergear) en vez de empujar directo a producción, sin la complejidad de GitFlow completo (sin ramas `release`/`hotfix`).

## Cómo funciona Bitbucket Pipelines

- Vive en un archivo **`bitbucket-pipelines.yml`** en la raíz del repo. Bitbucket lo detecta solo.
- Cada "pipeline" corre dentro de un contenedor Docker (tú eliges la imagen base, ej. `php:8.3`).
- Se activa por: push a una rama (`branches`), pull request (`pull-requests`), tag, o manualmente (`custom`).
- Un pipeline tiene **steps**; cada step es un contenedor limpio (a menos que uses `services` o compartas `artifacts` entre steps).
- **Caches**: evitan reinstalar `vendor/`/`node_modules/` en cada corrida si no cambiaron.
- **Artifacts**: pasan archivos generados en un step al siguiente step del mismo pipeline (no los usamos en este `bitbucket-pipelines.yml`, pero es una pieza estándar de Bitbucket Pipelines que vale la pena conocer).
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

No hay ningún step de Node/Vite en el pipeline — `public/build/` viaja comiteado en el repo (ver el cambio de estrategia sobre assets, arriba) y nadie en CI vuelve a compilarlo ni a verificarlo.

Un detalle propio de este proyecto: `tequia/app` y `tequia/vault` son **path repositories** (`app-modules/*`), no paquetes de Packagist — pero como en CI se clona el repo completo (a diferencia del build de Docker, que copia por capas), `composer install` los resuelve sin configuración extra.

### `bitbucket-pipelines.yml`

Ya está commiteado en la raíz del repo (no es solo un ejemplo de este doc). Resumen de su estructura — un step de CI (`tests`) que corre en toda PR, y un segundo step de deploy que solo corre en `branches: main`:

```yaml
pipelines:
  pull-requests:
    '**':
      - step: *tests

  branches:
    main:
      - step: *tests
      - step:
          name: Desplegar a producción
          deployment: production
          script:
            - pipe: atlassian/ssh-run:0.4.0
              variables:
                SSH_USER: $DEPLOY_SSH_USER
                SERVER: $DEPLOY_SSH_HOST
                SSH_KEY: $DEPLOY_SSH_KEY
                COMMAND: |
                  cd /home/xenthrall/cafe-del-tiempo &&
                  git pull origin main &&
                  sudo docker compose up -d --build &&
                  sudo docker compose exec -T app php artisan migrate --force
```

Notas:

- La imagen `php:8.3-cli` no trae `intl`/`pdo_pgsql`, por eso se instalan en el step — igual que tuvimos que ajustar en el `Dockerfile`. Si esto se vuelve lento, puedes construir y publicar tu propia imagen base con esas extensiones ya compiladas, y usarla aquí en vez de `php:8.3-cli`.
- El step de tests corre contra **SQLite en memoria** (ya configurado en `phpunit.xml`), no contra Postgres — no hace falta levantar un servicio de base de datos para correr Pest, lo cual mantiene el pipeline simple y rápido.
- No hay ningún step con Node/Vite — nada en el pipeline toca `public/build/`, solo lo trae el `git pull` del deploy porque ya está comiteado.
- `pull-requests: '**'` corre CI en cualquier PR (incluida `develop → main`) antes de que puedas aprobarla — combinado con un branch permission que exija "passing build", Bitbucket no te deja mergear si esto falla.
- El pipeline de `branches: main` vuelve a correr CI sobre el commit de merge ya en `main` (no confía ciegamente en el resultado de la PR) y, si pasa, encadena el deploy: `git pull` trae el código **y** el `public/build/` ya comiteado, y `docker compose up -d --build` construye la imagen con eso — sin Node, sin registry, sin nada más en el servidor ni en el pipeline.
- El step de deploy no tiene `trigger: manual`, así que se ejecuta automático apenas termina CI — esto coincide con lo que pediste ("al mergear a main, se despliega solo"). Si más adelante quieres un botón de confirmación antes de tocar producción, basta con agregar `trigger: manual` a ese step; con `deployment: production` puesto, Bitbucket igual te deja ver el historial de qué se desplegó y cuándo.
- El pipe `atlassian/ssh-run` es uno de varios "pipes" oficiales de Bitbucket (bloques reutilizables) para tareas comunes como SSH, rsync, o deploy a un proveedor específico — evita reescribir ese código a mano.
- El servidor necesita poder hacer `git pull origin main` sin pedir contraseña interactiva: configúrale su propia clave SSH (o un deploy key de solo lectura del repo) por separado de la clave que usa el pipeline para conectarse *a* él — son dos llaves distintas con dos propósitos distintos.
- El `COMMAND` antepone `sudo` a los comandos de Docker porque `xenthrall` (el usuario que va a usar `DEPLOY_SSH_USER`) necesita `sudo` para hablarle al daemon de Docker. Esto funciona sin quedarse colgado **solo porque `sudo` no le pide contraseña a ese usuario** (está en el grupo `sudo` con `NOPASSWD`, algo común en el usuario inicial de muchas VPS) — `ssh-run` ejecuta el `COMMAND` sin terminal interactiva, así que si `sudo` sí pidiera contraseña, se quedaría esperando un prompt que nunca podrías responder. Verifica esto antes de confiar en el pipeline:
  ```bash
  sudo -n true && echo "sudo sin contraseña: OK" || echo "sudo SÍ pide contraseña — esto va a fallar en el pipeline"
  ```
  Si el segundo mensaje aparece, la alternativa más simple es meter a `xenthrall` al grupo `docker` (`sudo usermod -aG docker xenthrall`, requiere volver a iniciar sesión) y quitar los `sudo` del `COMMAND` — así ni siquiera necesitas que `sudo` esté involucrado.

## Variables y secretos que vas a necesitar configurar

En Bitbucket: **Deployments → production → Environment variables** (scoped solo a ese environment, ya que solo se usan en el step de deploy):

| Variable | Dónde | Para qué |
|---|---|---|
| `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`, `DEPLOY_SSH_KEY` | Deployment (production) | Conectarse a tu servidor por SSH y ejecutar el `git pull` + `docker compose up -d --build` |

Como no hay registry, no necesitas credenciales de Docker Hub ni de ningún otro registry — un componente menos que asegurar.

El `APP_KEY`, las credenciales de Postgres, etc. **no viajan por el pipeline** — viven directamente en el `.env` de tu servidor (el mismo que ya armamos, gitignored, generado a partir de `.env.docker.example`), y el pipeline solo hace `git pull` + `docker compose up`, sin tocar esos secretos.

## Estado actual — qué ya está y qué falta

Ya resuelto (producción está viva en `https://cafe.tequia.dev`):

- Servidor con Docker + Docker Compose, repo clonado en `/home/xenthrall/cafe-del-tiempo`.
- `.env` del servidor con `APP_KEY`, credenciales de Postgres, `APP_URL` en HTTPS y `SESSION_SECURE_COOKIE=true` (ver [`docker-deploy.md`](docker-deploy.md)).
- `bitbucket-pipelines.yml` commiteado en la raíz, con CI (`tests`, PHP puro) + CD por SSH.
- `.gitignore` ya no excluye `public/build/` — lo comitas tú a mano tras compilar (`npm run build && git add public/build`).

Falta, en orden, para que el pipeline corra de verdad:

1. **Crear la rama `develop`** (hoy solo existe `main`):
   ```bash
   git checkout -b develop
   git push -u origin develop
   ```
2. **Confirmar que `sudo` no le pide contraseña a `xenthrall`** en el servidor (`sudo -n true`, ver la nota de arriba) — el `COMMAND` del pipeline ya asume esto. Si sí pide contraseña, o metes a `xenthrall` al grupo `docker` y quitas los `sudo` del `bitbucket-pipelines.yml`, o configuras `NOPASSWD` para ese usuario en sudoers.
3. **Generar el par de llaves SSH que usará el pipeline** para entrar al servidor (`DEPLOY_SSH_KEY`) — distinto del que usa el propio servidor para hacer `git pull` de Bitbucket. Genera un par nuevo, agrega la pública a `~/.ssh/authorized_keys` de `$DEPLOY_SSH_USER` en el servidor, y la privada la subes como variable segura en Bitbucket.
4. **Configurar en Bitbucket** (todo esto es en la web, no en el repo):
   - *Repository settings → Deployments*: crear el environment `production` y ahí las variables `DEPLOY_SSH_HOST`, `DEPLOY_SSH_USER`, `DEPLOY_SSH_KEY` (marcadas "Secured").
   - *Repository settings → Branch permissions*: `main` solo recibe cambios por pull request, con "passing build" obligatorio.
5. **Decidir si quieres CI también en pushes directos a `develop`** (sin PR) para feedback más temprano — se agregaría un bloque `branches: develop:` con solo el step de tests, sin deploy. Opcional, no bloquea nada.

Con 1-4 resueltos ya puedes abrir tu primera PR `develop → main` y ver el pipeline completo correr de punta a punta.
