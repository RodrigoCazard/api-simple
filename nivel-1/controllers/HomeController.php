<?php

/**
 * CONTROLLER DE INICIO
 * ------------------------------------------------------------------
 * Atiende la dirección raíz "/" y devuelve la lista de lo que se
 * puede pedir. Sirve para saber de una que la API está andando.
 */
class HomeController
{
    public function index()
    {
        Response::success([
            'api' => 'API de productos - UTU',
            'endpoints' => [
                'POST   /registro'           => 'crear una cuenta',
                'POST   /login'              => 'iniciar sesión y obtener el token',
                'GET    /perfil'             => 'mis datos (necesita token)',
                'GET    /productos'          => 'listar productos (?categoria=audio)',
                'GET    /productos/1'        => 'ver un producto',
                'POST   /productos'          => 'crear (necesita token)',
                'PUT    /productos/1'        => 'modificar (necesita token)',
                'DELETE /productos/1'        => 'borrar (solo admin)',
                'POST   /productos/1/vender' => 'vender: descuenta stock (necesita token)',
            ],
            'usuarios_de_prueba' => [
                'admin@utu.edu.uy / admin123 (admin)',
                'alumno@utu.edu.uy / alumno123 (usuario)',
            ],
        ]);
    }
}
