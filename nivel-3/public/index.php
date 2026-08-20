<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Si existe este archivo, Laravel responde en modo mantenimiento.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Carga automáticamente las clases de la aplicación y sus dependencias.
require __DIR__.'/../vendor/autoload.php';

// Inicia Laravel y procesa la petición HTTP actual.
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
