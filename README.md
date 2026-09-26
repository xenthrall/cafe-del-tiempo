# ☕ Café del Tiempo

> Suite personal de código abierto para proteger y organizar lo que más importa: una bóveda digital zero-knowledge y un módulo de finanzas personales, con más módulos en camino.

**[cafe.tequia.dev](https://cafe.tequia.dev)** · [Guía de instalación](https://cafe.tequia.dev/docs/instalacion) · [Licencia MIT](LICENSE)

## Qué es

Café del Tiempo es una plataforma organizada en módulos independientes. Hoy incluye:

| Módulo | Qué hace | Privacidad |
| --- | --- | --- |
| **Bóveda Digital** (`vault`) | Contraseñas, secretos, notas confidenciales y cápsulas del tiempo. | **Zero-knowledge.** Se cifra en el navegador (Argon2id + AES-256-GCM) con una contraseña maestra que nunca llega al servidor. El servidor solo guarda blobs cifrados. |
| **Finanzas Personales** (`finance`) | Cuentas, ingresos, gastos, transferencias y ajustes por contexto, con dashboards y reportes en Excel y PDF. | **No es zero-knowledge.** Los datos se guardan en la base de datos, asociados a tu usuario, para poder calcular dashboards y reportes. Están protegidos con control de acceso por usuario. |

## Dos formas de usarlo

- **Versión alojada:** crea una cuenta gratis en [cafe.tequia.dev](https://cafe.tequia.dev), sin instalar nada.
- **Self-hosted:** instala tu propia instancia con Docker o de forma manual. Es el mismo código y el mismo cifrado, pero con los datos en tu servidor.

El modo de cada instancia se controla con `APP_INSTANCE` en el `.env`:

| Valor | Comportamiento |
| --- | --- |
| `self-hosted` (por defecto) | Registro cerrado. Las cuentas las crea un administrador desde `/system`. |
| `hosted` | Registro público abierto en `/app/register`. |

## Instalación

La guía completa (requisitos, Docker, instalación manual, variables de entorno, colas, HTTPS, respaldos y actualizaciones) está en **[cafe.tequia.dev/docs/instalacion](https://cafe.tequia.dev/docs/instalacion)**.

### Con Docker (producción)

```bash
git clone https://github.com/xenthrall/cafe-del-tiempo.git
cd cafe-del-tiempo
cp .env.docker.example .env        # ajusta APP_URL, DB_PASSWORD, APP_INSTANCE
echo "base64:$(openssl rand -base64 32)"   # pega el resultado en APP_KEY
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan user:create --admin
```

La app queda en `http://TU_IP:9000/app`. Detalle paso a paso en [`docs/docker-deploy.md`](docs/docker-deploy.md).

### Local (desarrollo)

Requiere PHP 8.3+, Composer y Node.js 20.19+. Usa SQLite por defecto.

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
composer run dev
```

## Stack

- **Backend:** Laravel 13 (PHP 8.3+), con arquitectura modular vía [`internachi/modular`](https://github.com/InterNACHI/modular).
- **Panel:** Filament v5 en `/app` (uso personal) y `/system` (administración de la instancia).
- **Frontend:** Tailwind CSS v4 + Vite.
- **Base de datos:** SQLite (desarrollo) o PostgreSQL 16 (producción).
- **Respaldos:** `spatie/laravel-backup`, diarios, hacia Cloudflare R2.
- **Tests y estilo:** Pest y Pint.

## Estructura

```
app-modules/
├── app/       Panel Filament personal (/app): layout, login, registro y dashboard
├── system/    Panel de administración (/system), comando user:create y respaldos programados
├── vault/     Bóveda digital zero-knowledge
└── finance/   Finanzas personales
resources/views/landing/   Landing pública y documentación (cafe.tequia.dev)
docs/                      Documentación técnica y de producto
```

Cada dominio vive en su propio módulo. `app` es solo la capa de panel y no contiene lógica de negocio.

## Documentación

| Documento | Contenido |
| --- | --- |
| [`docs/vision.md`](docs/vision.md) | Qué es el proyecto, principios, decisiones y pendientes |
| [`docs/vault.md`](docs/vault.md) | Diseño criptográfico y modelo de datos de la bóveda |
| [`docs/finance.md`](docs/finance.md) | Modelo de datos y reglas del módulo de finanzas |
| [`docs/docker-deploy.md`](docs/docker-deploy.md) | Despliegue manual con Docker |
| [`docs/ci-cd.md`](docs/ci-cd.md) | Pipeline de Bitbucket (espejo para practicar CI/CD) |

## Desarrollo

```bash
php artisan test --compact      # tests
vendor/bin/pint                 # estilo de código
npm run build                   # compilar assets (public/build se versiona)
```

## Licencia

[MIT](LICENSE) · Un proyecto de [Tequia](https://tequia.dev).
