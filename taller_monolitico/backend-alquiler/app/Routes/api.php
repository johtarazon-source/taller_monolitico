<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use Slim\App;

/**
 * Registro de rutas de la API REST.
 * Las rutas de vehiculos, clientes y reservas se agregan una a una en
 * las fases siguientes.
 */
return function (App $app): void {
    $app->post('/auth/login', [AuthController::class, 'login']);
    $app->post('/auth/logout', [AuthController::class, 'logout']);
};
