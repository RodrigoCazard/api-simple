<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Mantiene el mismo formato JSON de los niveles anteriores.
 * response()->json() es el helper de Laravel que agrega cabeceras y status.
 */
final class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $status = 200): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'mensaje' => $message,
            'datos' => $data,
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'mensaje' => $message,
            'errores' => $errors,
        ], $status);
    }
}
