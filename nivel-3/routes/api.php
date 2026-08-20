<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Laravel agrega automáticamente el prefijo /api a este archivo.
Route::get('/productos', [ProductController::class, 'index']);
Route::get('/productos/{product}', [ProductController::class, 'show']);

// Sanctum autentica estas rutas mediante la cookie de sesión HttpOnly.
Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/perfil', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/productos', [ProductController::class, 'store']);
    Route::put('/productos/{product}', [ProductController::class, 'update']);
    Route::post('/productos/{product}/vender', [ProductController::class, 'sell']);

    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])
        ->middleware('admin');
});
