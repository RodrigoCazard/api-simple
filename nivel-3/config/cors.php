<?php

return [

    // Rutas que puede llamar el frontend desde otro puerto o subdominio.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Nunca se usa "*" junto con cookies: se declara el origen exacto.
    'allowed_origins' => [env('FRONTEND_ORIGIN', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Permite que el navegador envíe la cookie de sesión a la API.
    'supports_credentials' => true,

];
