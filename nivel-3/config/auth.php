<?php

use App\Models\User;

return [

    // Guard y configuración de recuperación utilizados por defecto.

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    // Un guard define cómo se mantiene la identidad del usuario autenticado.

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    // El provider indica que los usuarios se obtienen mediante el modelo User.

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],

    // Ajustes para una futura recuperación de contraseñas (no usada acá).

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    // Segundos durante los que vale una confirmación reciente de contraseña.

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
