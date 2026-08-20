<?php

/**
 * HELPERS (funciones sueltas, sin clase)
 * ==================================================================
 * AuthController y ProductController necesitan las mismas dos cosas:
 * leer el JSON del pedido, y exigir login o rol de admin. En vez de
 * una clase para agruparlas, acá son simplemente funciones de PHP:
 * cada controller les hace un require y las llama directo
 * (requireLogin(), no $this->requireLogin()).
 *
 * Cada método de cada controller decide por su cuenta si necesita
 * requireLogin() o requireAdmin(), llamándola al principio (mirá
 * ProductController::store(), por ejemplo). En nivel-2 esa decisión
 * no está en el controller: se declara en routes.php y la aplica un
 * middleware ANTES de que el controller se entere del pedido — mirá
 * core/AuthMiddleware.php de nivel-2 para comparar los dos enfoques.
 * ==================================================================
 */

/**
 * Lee el JSON que mandó el cliente y lo convierte en arreglo.
 *
 * Los datos de un POST o un PUT en formato JSON no llegan en $_POST:
 * hay que leerlos del "cuerpo" del pedido con php://input.
 */
function requestData(): array
{
    $json = file_get_contents('php://input');

    // Algunos pedidos, como un POST sin datos, pueden traer el cuerpo vacío.
    if ($json === false || trim($json) === '') {
        return [];
    }

    $data = json_decode($json, true);

    // Un JSON mal escrito es distinto de no mandar datos. Lo avisamos con
    // un error 400 para que el cliente pueda corregir su petición.
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        Response::error('El cuerpo tiene que ser un objeto JSON válido.', 400);
    }

    return $data;
}

/**
 * EXIGE que haya un token válido.
 *
 * Si lo hay, devuelve los datos del usuario (id, nombre, rol).
 * Si no, contesta 401 y el programa TERMINA ahí mismo (acordate que
 * Response::error() hace exit).
 *
 * 401 = "no sé quién sos"
 */
function requireLogin()
{
    $user = Token::read();

    if ($user === null) {
        Response::error('Tenés que iniciar sesión. Mandá el token en la cabecera Authorization.', 401);
    }

    return $user;
}

/**
 * EXIGE que además sea administrador.
 *
 * Primero se fija que esté logueado (reutiliza la función de arriba)
 * y después mira el rol.
 *
 * 403 = "sé quién sos, pero no podés hacer esto"
 *
 * ¡OJO CON LA DIFERENCIA! Es la confusión más común:
 *   401 = AUTENTICACIÓN -> ¿quién sos?
 *   403 = AUTORIZACIÓN  -> ¿tenés permiso?
 */
function requireAdmin()
{
    $user = requireLogin();

    if ($user['rol'] !== 'admin') {
        Response::error('Solo un administrador puede hacer esto.', 403);
    }

    return $user;
}
