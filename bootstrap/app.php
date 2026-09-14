<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // La app corre detrás de nginx (mismo docker-compose) y, en producción,
        // de un túnel de Cloudflare que la expone en HTTPS — ninguno de los
        // dos tiene una IP fija conocida de antemano (la del contenedor nginx
        // es dinámica), así que se confía en cualquier proxy para leer
        // X-Forwarded-Proto y que Laravel/Livewire generen URLs con https.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
