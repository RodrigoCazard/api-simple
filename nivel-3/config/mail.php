<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Servicio de correo predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción define el servicio usado para enviar correos, salvo que se
    | indique otro explícitamente. Los servicios adicionales se configuran
    | dentro del arreglo "mailers".
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de los servicios de correo
    |--------------------------------------------------------------------------
    |
    | Aquí se configuran los servicios de correo disponibles y sus opciones.
    | Se pueden agregar otros cuando la aplicación los necesite.
    |
    | Laravel admite distintos transportes para entregar los mensajes. Cada
    | servicio debe indicar el transporte que utiliza.
    |
    | Admitidos: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // Postmark permite agregar aquí opciones propias de su cliente.
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Dirección global del remitente
    |--------------------------------------------------------------------------
    |
    | Define el nombre y la dirección que se usarán como remitente en todos
    | los correos enviados por la aplicación.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];
