<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Activa sesiones, cookies y CSRF para peticiones de nuestra SPA.
        $middleware->statefulApi();

        // Un alias permite escribir ->middleware('admin') en routes/api.php.
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Laravel centraliza acá errores que pueden ocurrir en distintas rutas.
         * Así los controllers no repiten try/catch y la API mantiene su JSON.
         */
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error('Revisá los datos enviados.', 422, $exception->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error('Tenés que iniciar sesión.', 401);
            }
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($request->expectsJson() && $exception->getStatusCode() === 419) {
                return ApiResponse::error('La sesión o el token CSRF vencieron. Volvé a pedir /sanctum/csrf-cookie.', 419);
            }
        });
    })->create();
