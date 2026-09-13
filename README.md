# ☕ Café del Tiempo

> Bóveda digital privada para proteger, preservar y recuperar la información que más importa.

**Estado: idea en fase de diseño y desarrollo temprano.** El alcance y la arquitectura de producto todavía se están definiendo — ver [`docs/vision.md`](docs/vision.md).

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
