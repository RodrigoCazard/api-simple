<?php

/**
 * ==================================================================
 * INDEX.PHP - NIVEL 1: "SWITCH GIGANTE" (sin clase Router)
 * ==================================================================
 * Esta es una app INDEPENDIENTE, hermana de la de nivel-2/. Tiene su
 * propia copia de config.php, .env, vendor/, models/, repositories/,
 * services/ y controllers/: no depende de ningún archivo de la otra
 * carpeta.
 *
 * Las dos trabajan con los mismos datos y reglas principales, pero la
 * autenticación viaja de forma distinta y nivel-2 agrega /logout para
 * borrar su cookie. Acá el enrutado y los controles de acceso quedan
 * explícitos antes de pasar a las herramientas de nivel-2:
 *
 *   ENRUTAR         nivel-1 -> switch gigante acá mismo
 *                   nivel-2 -> clase Router (core/Router.php) + routes.php
 *
 *   AYUDAS COMUNES  nivel-1 -> funciones sueltas (core/helpers.php)
 *                   nivel-2 -> clase Controller de la que heredan
 *
 *   LOGIN / ADMIN   nivel-1 -> cada método llama requireLogin()/
 *                              requireAdmin() al principio
 *                   nivel-2 -> un middleware lo resuelve ANTES de
 *                              llegar al controller (core/AuthMiddleware.php)
 *
 * ------------------------------------------------------------------
 * ¿POR QUÉ EXISTE UNA VERSIÓN "SIN ROUTER"?
 *
 * El Router (la clase) es más prolijo y es como enrutan de verdad
 * Laravel, Slim, etc., pero para entenderlo hay que saber arrays,
 * explode() e indirección de clases ($class = 'Foo'; new $class()).
 *
 * Esta versión no usa nada de eso: es un switch de arriba a abajo,
 * como el código que se escribiría ANTES de conocer la idea de
 * "router". Es más largo y se repite más, pero cada caso se lee
 * de corrido sin saltar a otro archivo.
 * ==================================================================
 */

// ------------------------------------------------------------------
// 1) CARGAR LOS ARCHIVOS
// Todos viven ADENTRO de nivel-1/, por eso alcanza con partir de __DIR__.
// ------------------------------------------------------------------
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

// Core: PERO SIN Router.php, porque acá no lo usamos.
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Token.php';
require_once __DIR__ . '/core/helpers.php';

// Models
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Product.php';

// Repositories
require_once __DIR__ . '/repositories/Repository.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/repositories/ProductRepository.php';

// Services
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/ProductService.php';

// Controllers
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';

// ------------------------------------------------------------------
// 2) CORS
// ------------------------------------------------------------------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ------------------------------------------------------------------
// 3) MÉTODO Y DIRECCIÓN
// ------------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];

/**
 * $_SERVER['REQUEST_URI'] trae la dirección TAL CUAL la pidieron,
 * con el query string incluido si vino uno. Ejemplo:
 *
 *   /productos/3?categoria=audio
 *
 * parse_url() la separa en sus partes (path, query, host, etc.), y
 * con PHP_URL_PATH le decimos que solo nos interesa la parte de
 * ADELANTE del "?": el path.
 *
 *   parse_url('/productos/3?categoria=audio', PHP_URL_PATH)
 *   ->  '/productos/3'
 *
 * El query string ('categoria=audio') no lo tocamos acá: a eso se
 * accede aparte con $_GET (mirá ProductController::index()).
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Partimos la dirección en pedazos para poder sacar el {id} cuando
 * haga falta. Ejemplo: '/productos/3' -> ['productos', '3']
 *
 * array_values(array_filter(...)) saca los pedazos vacíos que deja
 * explode cuando la dirección empieza o termina con "/".
 */
$parts = array_values(array_filter(explode('/', $path), fn($p) => $p !== ''));
$count = count($parts);

// ------------------------------------------------------------------
// 4) EL SWITCH GIGANTE
// ------------------------------------------------------------------
// switch(true) es un truco: en vez de comparar $method contra cada
// "case", cada "case" es una condición completa (method + forma de
// la ruta) que da true o false. El switch ejecuta el PRIMER case
// que dé true. Es lo mismo que un if/elseif/elseif/... pero más
// ordenado de leer cuando hay muchos casos.
//
// El orden importa: rutas más específicas primero.
// ------------------------------------------------------------------
/**
 * El try/catch mantiene los errores internos fuera de la respuesta.
 * El alumno sigue viendo el switch completo; solamente agregamos una red
 * de seguridad alrededor de todo el recorrido del pedido.
 */
try {
    switch (true) {

        // ---- Inicio ------------------------------------------------
        case $method === 'GET' && $count === 0:
            // GET /
            (new HomeController())->index();
            break;

        // ---- Entrar al sistema -------------------------------------
        case $method === 'POST' && $count === 1 && $parts[0] === 'registro':
            // POST /registro
            (new AuthController())->register();
            break;

        case $method === 'POST' && $count === 1 && $parts[0] === 'login':
            // POST /login
            (new AuthController())->login();
            break;

        case $method === 'GET' && $count === 1 && $parts[0] === 'perfil':
            // GET /perfil
            (new AuthController())->profile();
            break;

        // ---- Productos ---------------------------------------------
        case $method === 'GET' && $count === 1 && $parts[0] === 'productos':
            // GET /productos
            (new ProductController())->index();
            break;

        case $method === 'GET' && $count === 2 && $parts[0] === 'productos':
            // GET /productos/3  ->  $parts[1] es el id ('3')
            (new ProductController())->show($parts[1]);
            break;

        case $method === 'POST' && $count === 1 && $parts[0] === 'productos':
            // POST /productos
            (new ProductController())->store();
            break;

        case $method === 'PUT' && $count === 2 && $parts[0] === 'productos':
            // PUT /productos/3
            (new ProductController())->update($parts[1]);
            break;

        case $method === 'DELETE' && $count === 2 && $parts[0] === 'productos':
            // DELETE /productos/3
            (new ProductController())->destroy($parts[1]);
            break;

        // Una acción que no es CRUD: vender descuenta stock.
        case $method === 'POST' && $count === 3 && $parts[0] === 'productos' && $parts[2] === 'vender':
            // POST /productos/3/vender  ->  $parts[1] es el id ('3')
            (new ProductController())->sell($parts[1]);
            break;

        // ---- Nada coincidió ---------------------------------------
        default:
            Response::error('No existe esa dirección, o no se puede usar con ' . $method . '.', 404);
    }
} catch (PDOException $exception) {
    // El detalle queda en el log del servidor, nunca en el JSON público.
    error_log($exception->getMessage());

    Response::error('Ocurrió un error interno con la base de datos.', 500);
} catch (Throwable $exception) {
    error_log($exception->getMessage());

    Response::error('Ocurrió un error interno.', 500);
}
