<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Registra servicios propios dentro del contenedor de Laravel. */
    public function register(): void
    {
        // Esta API básica no necesita registrar servicios manualmente.
    }

    /** Ejecuta configuración adicional después de registrar los servicios. */
    public function boot(): void
    {
        // No hay configuración adicional para este ejemplo.
    }
}
