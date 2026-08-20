<?php

/**
 * SERVICE DE AUTENTICACIÓN
 * ==================================================================
 * Las reglas de "quién puede entrar al sistema":
 *
 *   - el email no se puede repetir
 *   - la contraseña se guarda como un hash, nunca como la escribieron
 *   - el que se registra solo es siempre rol "usuario"
 *   - una cuenta deshabilitada no puede entrar
 * ==================================================================
 */
class AuthService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    /**
     * Registrar una cuenta nueva.
     */
    public function register($name, $email, $password)
    {
        // REGLA: el email es único.
        if ($this->repository->findByEmail($email) !== null) {
            Response::error('Ya existe una cuenta con ese email.', 400);
        }

        /**
         * REGLA: la contraseña se guarda como un HASH.
         * password_hash() crea una representación de una sola dirección:
         * no se puede recuperar la contraseña original a partir del hash.
         * Si mañana roban la base, no encuentran las claves en texto plano.
         *
         * Y ojo con esto: el rol lo ponemos nosotros ('usuario', dentro
         * del repository). Si lo aceptáramos del pedido, cualquiera se
         * registraría como admin.
         */
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $user = $this->repository->create($name, $email, $passwordHash);

        return $user->toArray();
    }

    /**
     * Iniciar sesión: verifica la contraseña y devuelve el token.
     */
    public function login($email, $password)
    {
        $user = $this->repository->findByEmail($email);

        /**
         * DETALLE DE SEGURIDAD:
         * el mensaje es el mismo si el email no existe o si la
         * contraseña está mal. Si dijéramos "ese email no existe", le
         * estaríamos regalando a un atacante la lista de qué emails
         * están registrados en el sistema.
         */
        if ($user === null || !$user->checkPassword($password)) {
            Response::error('Email o contraseña incorrectos.', 401);
        }

        // REGLA: una cuenta deshabilitada no entra.
        if (!$user->isActive()) {
            Response::error('Tu cuenta está deshabilitada.', 403);
        }

        return [
            'token'   => Token::create($user),
            'usuario' => $user->toArray(),
        ];
    }

    /**
     * Los datos de un usuario.
     */
    public function getProfile($id)
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            Response::error('El usuario ya no existe.', 404);
        }

        return $user->toArray();
    }
}
