<?php

declare(strict_types=1);

use App\Controllers\Api\AuthController;
use App\Controllers\Api\ClienteController;
use App\Controllers\Api\ReporteController;
use App\Controllers\Api\ReservaController;
use App\Controllers\Api\VehiculoController;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * API REST (JSON) bajo /api. Todo, excepto el login, exige token Bearer.
 */
return function (App $app): void {
    $app->group('/api', function (RouteCollectorProxy $api): void {
        $api->post('/auth/login', [AuthController::class, 'login']);

        $api->group('', function (RouteCollectorProxy $group): void {
            $group->post('/auth/logout', [AuthController::class, 'logout']);

            $group->get('/resumen', [ReporteController::class, 'resumen']);

            // "disponibles" va antes de "{id}" para que no se interprete como un id.
            $group->get('/vehiculos/disponibles', [VehiculoController::class, 'disponibles']);
            $group->get('/vehiculos', [VehiculoController::class, 'index']);
            $group->post('/vehiculos', [VehiculoController::class, 'store']);
            $group->get('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'show']);
            $group->put('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'update']);
            $group->delete('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'destroy']);
            $group->get('/vehiculos/{id:[0-9]+}/historial', [VehiculoController::class, 'historial']);

            $group->get('/clientes', [ClienteController::class, 'index']);
            $group->post('/clientes', [ClienteController::class, 'store']);
            $group->get('/clientes/{id:[0-9]+}', [ClienteController::class, 'show']);
            $group->put('/clientes/{id:[0-9]+}', [ClienteController::class, 'update']);
            $group->delete('/clientes/{id:[0-9]+}', [ClienteController::class, 'destroy']);
            $group->get('/clientes/{id:[0-9]+}/historial', [ClienteController::class, 'historial']);

            $group->get('/reservas', [ReservaController::class, 'index']);
            $group->post('/reservas', [ReservaController::class, 'store']);
            $group->get('/reservas/{id:[0-9]+}', [ReservaController::class, 'show']);
            $group->post('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'devolver']);
            $group->post('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'cancelar']);
        })->add(AuthMiddleware::class);
    });
};
