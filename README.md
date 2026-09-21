# ☕ Café del Tiempo

> Plataforma personal auto-alojada para preservar y gestionar la información que más importa, construida como módulos independientes.

**Estado: idea en fase de diseño y desarrollo temprano.** El primer módulo es una bóveda de credenciales (`vault`); ya hay un segundo módulo en planteamiento, finanzas personales (`finance`). El alcance y la arquitectura de producto todavía se están definiendo — ver [`docs/vision.md`](docs/vision.md) (visión general y roadmap por módulo), [`docs/vault.md`](docs/vault.md) y [`docs/finance.md`](docs/finance.md).

## Stack

Laravel 13 (PHP 8.3+) con arquitectura modular ([`internachi/modular`](https://github.com/InterNACHI/modular)), Filament v5, Tailwind CSS v4 + Vite, Pest, SQLite por defecto.

## Puesta en marcha

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev      # o: npm run build
php artisan serve
```

Tests: `php artisan test --compact` · Estilo: `vendor/bin/pint --format agent`

## Licencia

[MIT](LICENSE)
