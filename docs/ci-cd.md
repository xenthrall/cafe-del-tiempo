# CI/CD con Bitbucket Pipelines

El remoto principal del proyecto es **GitHub**. Además existe un espejo en **Bitbucket** que se usa para practicar Bitbucket Pipelines; es un entorno personal y no es obligatorio para desplegar. El despliegue normal es el manual de [`docker-deploy.md`](docker-deploy.md). No hay GitHub Actions por ahora.

Última actualización: 2026-09-26.

## Estado actual de `bitbucket-pipelines.yml`

| Pieza | Estado | Qué hace |
| --- | --- | --- |
| Step `tests` | Definido, **desactivado** | Instala PHP 8.3 con `intl`, `zip` y `pdo_pgsql`, corre `composer install`, `vendor/bin/pint --test` y `php artisan test --compact` (SQLite en memoria). No está enganchado a ningún pipeline para ahorrar minutos de build. |
| Pipeline `custom: deploy-production` | Activo, **manual** | Se lanza desde la interfaz de Bitbucket. Entra al servidor por SSH (pipe `atlassian/ssh-run`) y ejecuta el mismo flujo de actualización de `docker-deploy.md`. |

Comando que ejecuta el deploy en el servidor:

```bash
cd /home/xenthrall/cafe-del-tiempo &&
git pull origin main &&
sudo docker compose up -d --build &&
sudo docker compose exec -T app php artisan migrate --force
```

## Variables necesarias

Se configuran en Bitbucket, en *Repository settings → Deployments → production*, marcadas como "Secured":

| Variable | Para qué |
| --- | --- |
| `DEPLOY_SSH_HOST` | IP o dominio del servidor. |
| `DEPLOY_SSH_USER` | Usuario con el que entra el pipeline. |
| `DEPLOY_SSH_KEY` | Clave privada SSH del pipeline (la pública va en `~/.ssh/authorized_keys` del servidor). |

Los secretos de la aplicación (`APP_KEY`, credenciales de Postgres y R2) no pasan por el pipeline: viven en el `.env` del servidor.

## Requisitos en el servidor

- **`sudo` sin contraseña** para el usuario del pipeline, porque `ssh-run` no tiene terminal interactiva. Verifícalo con `sudo -n true`. La alternativa es agregar el usuario al grupo `docker` (`sudo usermod -aG docker <usuario>`) y quitar los `sudo` del comando.
- **`git pull` sin contraseña:** el servidor necesita su propia clave o deploy key de solo lectura para su remoto `origin`. Es distinta de la clave con la que el pipeline entra al servidor.

## Assets compilados

`public/build/` se compila en local (`npm run build`) y se versiona en el repo. Ni el pipeline ni el servidor necesitan Node: el `git pull` ya trae los assets. El riesgo es olvidar recompilar antes de hacer commit de un cambio de frontend.

## Activar la CI

Para correr los tests en cada pull request, agrega en `pipelines`:

```yaml
pull-requests:
  '**':
    - step: *tests
```

Para exigir que `main` solo reciba cambios por PR con los tests en verde, configura *Repository settings → Branch permissions* en Bitbucket.
