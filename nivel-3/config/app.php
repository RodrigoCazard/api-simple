<?php

return [

    // Nombre utilizado por Laravel en mensajes, notificaciones y herramientas.

    'name' => env('APP_NAME', 'Laravel'),

    // Entorno actual: development para aprender o production para publicar.

    'env' => env('APP_ENV', 'production'),

    // Muestra errores detallados. Debe ser false en producción.

    'debug' => (bool) env('APP_DEBUG', false),

    // Dirección base usada para generar enlaces desde comandos de Artisan.

    'url' => env('APP_URL', 'http://localhost'),

    // Zona horaria interna; UTC evita ambigüedades entre servidores.

    'timezone' => 'UTC',

    // Idiomas principal, alternativo y de los datos falsos creados por Faker.

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    // Algoritmo y clave de cifrado. APP_KEY debe ser aleatoria y secreta.

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    // El modo mantenimiento puede guardarse en un archivo o en caché.

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
