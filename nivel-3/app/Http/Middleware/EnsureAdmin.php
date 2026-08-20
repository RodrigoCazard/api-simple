<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Middleware que permite continuar solamente a usuarios administradores. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return ApiResponse::error('Esta operación requiere el rol administrador.', 403);
        }

        // $next entrega la petición al próximo middleware o al controller.
        return $next($request);
    }
}
