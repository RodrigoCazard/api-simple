<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Registro, login, perfil y logout utilizando Laravel Sanctum. */
class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'nombre' => trim($data['nombre']),
            'email' => strtolower(trim($data['email'])),
            // El cast `hashed` de User guarda un hash, nunca el texto original.
            'password' => $data['clave'],
            'rol' => 'usuario',
            'activo' => true,
        ]);

        return ApiResponse::success(
            $user,
            'Cuenta creada.',
            201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'email' => strtolower(trim($request->validated('email'))),
            'password' => $request->validated('clave'),
            'activo' => true,
        ];

        if (! Auth::attempt($credentials)) {
            return ApiResponse::error('Email o contraseña incorrectos.', 401);
        }

        // Cambia el identificador de sesión para impedir ataques de fijación.
        $request->session()->regenerate();

        return ApiResponse::success([
            'usuario' => $request->user(),
        ], 'Sesión iniciada.');
    }

    public function profile(Request $request): JsonResponse
    {
        return ApiResponse::success($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        // Invalida los datos anteriores y crea un nuevo token CSRF.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ApiResponse::success(null, 'Sesión cerrada.');
    }
}
