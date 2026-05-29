<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Recargar el .env en modo mutable para sobreescribir variables de sistema
// vacías o incorrectas que Plesk inyecta en el entorno del proceso PHP
// (e.g. APP_KEY="", DB_HOST="localhost"). Sin esto, Dotenv::createImmutable()
// respeta los valores del sistema y los del .env son ignorados.
if (file_exists($__envPath = dirname(__DIR__).'/.env')) {
    \Dotenv\Dotenv::createMutable(dirname(__DIR__))->load();
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->alias([
            'admin'      => \App\Http\Middleware\EnsureAdmin::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'feature'    => \App\Http\Middleware\RequireFeature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Asegurar que las respuestas de error en rutas API siempre incluyan
        // los headers CORS, incluso cuando la excepción bypasea el middleware.
        $exceptions->respond(function (\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $origin = $request->headers->get('Origin', '*');
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
            }
            return $response;
        });
    })->create();
