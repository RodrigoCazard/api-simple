<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [

    // Orígenes de nuestros frontends autorizados a usar sesiones con cookies.

    'stateful' => explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        'localhost:5173,localhost:8003,127.0.0.1:5173,127.0.0.1:8003'
    )),

    // Sanctum usa el guard web, que autentica mediante la sesión de Laravel.

    'guard' => ['web'],

    // Middleware que cifra cookies, autentica la sesión y comprueba el CSRF.

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];
