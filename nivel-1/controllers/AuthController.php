<?php

/**
 * CONTROLLER DE AUTENTICACIÓN
 * ==================================================================
 * Registro, login y perfil.
 *
 * EL CIRCUITO COMPLETO:
 *
 *   1. El usuario manda email y contraseña          -> POST /login
 *   2. El SERVICE busca el usuario y verifica la contraseña
 *   3. Si está todo bien, devuelve un TOKEN
 *   4. El cliente guarda ese token
 *   5. En cada pedido lo manda: Authorization: Bearer <token>
 *
 * requestData() y requireLogin() están en core/helpers.php.
 * ==================================================================
 */
class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    /**
     * POST /registro
     * Recibe: { "nombre": "...", "email": "...", "clave": "..." }
     */
    public function register()
    {
        $data = requestData();

        // Primero conservamos los valores tal como llegaron. Así podemos
        // comprobar su tipo antes de usar funciones como trim() o strlen().
        $name     = $data['nombre'] ?? null;
        $email    = $data['email'] ?? null;
        $password = $data['clave'] ?? null;

        // ---- VALIDACIÓN ------------------------------------------
        // NUNCA hay que confiar en lo que manda el cliente.
        $errors = [];

        if (!is_string($name) || strlen(trim($name)) < 3) {
            $errors[] = 'El nombre tiene que tener al menos 3 letras.';
        }

        // filter_var es la forma correcta de validar un email en PHP.
        if (!is_string($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido.';
        }

        if (!is_string($password) || strlen($password) < 6) {
            $errors[] = 'La contraseña tiene que tener al menos 6 caracteres.';
        }

        if (count($errors) > 0) {
            Response::error('Revisá los datos.', 400, $errors);
        }

        // Si el email ya existe o no, lo decide el SERVICE:
        // para saberlo hay que ir a buscar a la base.
        $user = $this->service->register(trim($name), trim($email), $password);

        Response::success($user, 'Cuenta creada.', 201);
    }

    /**
     * POST /login
     * Recibe: { "email": "...", "clave": "..." }
     */
    public function login()
    {
        $data = requestData();

        $email    = $data['email'] ?? null;
        $password = $data['clave'] ?? null;

        if (!is_string($email) || trim($email) === ''
            || !is_string($password) || $password === '') {
            Response::error('Faltan el email o la contraseña.', 400);
        }

        $session = $this->service->login(trim($email), $password);

        Response::success($session, 'Sesión iniciada. Guardá el token y mandalo en cada pedido.');
    }

    /**
     * GET /perfil   (hay que estar logueado)
     * Devuelve los datos del dueño del token.
     */
    public function profile()
    {
        // Si no hay token válido, este método corta acá con un 401.
        $tokenData = requireLogin();

        $user = $this->service->getProfile($tokenData['id']);

        Response::success($user);
    }
}
