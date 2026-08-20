<?php

use App\Http\Controllers\AuthController;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

/**
 * El login y el registro usan el grupo web porque necesitan sesión y CSRF.
 * Aunque estén en este archivo, conservan sus direcciones /api/...
 */
Route::post('/api/registro', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/api/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/', function () {
    if (app()->isProduction()) {
        return ApiResponse::success([
            'api' => 'API de productos - UTU - Nivel 3',
        ], 'API funcionando.');
    }

    return ApiResponse::success([
        'api' => 'API de productos - UTU - Nivel 3 con Laravel',
        'entorno' => app()->environment(),
        'aviso_ia' => 'Este nivel fue creado con ayuda de IA y puede contener errores.',
        'aprendizaje' => 'Si usás Laravel, investigá la documentación oficial y aprendé por tu cuenta; no copies sin comprender.',
        'endpoints' => [
            'POST   /api/registro' => 'crear una cuenta',
            'GET    /sanctum/csrf-cookie' => 'iniciar la protección CSRF',
            'POST   /api/login' => 'iniciar la sesión con una cookie',
            'POST   /api/logout' => 'cerrar la sesión',
            'GET    /api/perfil' => 'ver el usuario autenticado',
            'GET    /api/productos' => 'listar productos',
            'GET    /api/productos/1' => 'ver un producto',
            'POST   /api/productos' => 'crear (requiere sesión)',
            'PUT    /api/productos/1' => 'modificar (requiere sesión)',
            'DELETE /api/productos/1' => 'borrar (solo admin)',
            'POST   /api/productos/1/vender' => 'descontar stock',
        ],
        'usuarios_de_prueba' => [
            'admin@utu.edu.uy / admin123 (admin)',
            'alumno@utu.edu.uy / alumno123 (usuario)',
        ],
    ]);
});
