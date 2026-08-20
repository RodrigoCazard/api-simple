<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Controlador de sesión predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo se guardan las sesiones de las peticiones.
    | Laravel admite diferentes sistemas de almacenamiento. Para este ejemplo
    | se usan archivos porque son fáciles de inspeccionar y no requieren tabla.
    |
    | Admitidos: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Duración de la sesión
    |--------------------------------------------------------------------------
    |
    | Indica cuántos minutos puede permanecer inactiva una sesión antes de
    | vencer. "expire_on_close" permite cerrarla al cerrar el navegador.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Cifrado de la sesión
    |--------------------------------------------------------------------------
    |
    | Si esta opción está activa, Laravel cifra automáticamente los datos de
    | la sesión antes de almacenarlos.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Ubicación de los archivos de sesión
    |--------------------------------------------------------------------------
    |
    | Cuando se utiliza el controlador "file", aquí se indica la carpeta
    | donde se guardan los archivos de sesión.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Conexión de base de datos para las sesiones
    |--------------------------------------------------------------------------
    |
    | Con los controladores "database" o "redis", esta opción permite elegir
    | una conexión definida en la configuración de la base de datos.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Tabla de sesiones
    |--------------------------------------------------------------------------
    |
    | Cuando se utiliza "database", esta opción indica en qué tabla se
    | almacenan las sesiones.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Almacén de caché para las sesiones
    |--------------------------------------------------------------------------
    |
    | Si las sesiones utilizan caché, aquí se elige uno de los almacenes
    | definidos en la configuración de caché.
    |
    | Afecta a: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Sorteo para limpiar sesiones
    |--------------------------------------------------------------------------
    |
    | Algunos controladores deben eliminar manualmente las sesiones vencidas.
    | Estos números indican la probabilidad de hacerlo en cada petición: de
    | forma predeterminada, 2 oportunidades entre 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nombre de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Permite cambiar el nombre de la cookie de sesión creada por Laravel.
    | Normalmente no es necesario modificarlo y hacerlo no mejora la seguridad.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Determina en qué rutas del sitio estará disponible la cookie. En general
    | se utiliza la raíz de la aplicación.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la cookie de sesión
    |--------------------------------------------------------------------------
    |
    | Determina para qué dominio y subdominios estará disponible la cookie.
    | Normalmente no es necesario modificarlo.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies exclusivas de HTTPS
    |--------------------------------------------------------------------------
    |
    | Si esta opción vale "true", el navegador solo enviará la cookie de
    | sesión mediante una conexión HTTPS.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | Acceso exclusivo mediante HTTP
    |--------------------------------------------------------------------------
    |
    | Si esta opción vale "true", JavaScript no podrá leer la cookie. Es una
    | protección de seguridad que normalmente debe permanecer activa.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies SameSite
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el comportamiento de las cookies en peticiones entre
    | sitios y ayuda a reducir ataques CSRF. El valor predeterminado es "lax".
    |
    | Referencia: https://developer.mozilla.org/es/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Admitidos: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Cookies particionadas
    |--------------------------------------------------------------------------
    |
    | Esta opción vincula la cookie al sitio principal cuando se usa entre
    | sitios. El navegador exige que sea segura y que SameSite valga "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | Serialización de la sesión
    |--------------------------------------------------------------------------
    |
    | Controla el formato usado para guardar los datos de sesión. JSON es la
    | opción predeterminada. El formato "php" admite objetos, pero puede
    | aumentar el riesgo si se filtra APP_KEY.
    |
    | Admitidos: "json", "php"
    |
    */

    'serialization' => 'json',

];
