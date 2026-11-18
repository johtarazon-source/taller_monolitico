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
return function (App $aplicacion): void {
    $aplicacion->group('/api', function (RouteCollectorProxy $grupoApi): void {
        $grupoApi->post('/auth/login', [AuthController::class, 'login']);

        $grupoApi->group('', function (RouteCollectorProxy $grupoProtegido): void {
            $grupoProtegido->post('/auth/logout', [AuthController::class, 'logout']);

            $grupoProtegido->get('/resumen', [ReporteController::class, 'resumen']);

            // "disponibles" va antes de "{id}" para que no se interprete como un id.
            $grupoProtegido->get('/vehiculos/disponibles', [VehiculoController::class, 'disponibles']);
            $grupoProtegido->get('/vehiculos', [VehiculoController::class, 'index']);
            $grupoProtegido->post('/vehiculos', [VehiculoController::class, 'store']);
            $grupoProtegido->get('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'show']);
            $grupoProtegido->put('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'update']);
            $grupoProtegido->delete('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'destroy']);
            $grupoProtegido->get('/vehiculos/{id:[0-9]+}/historial', [VehiculoController::class, 'historial']);

            $grupoProtegido->get('/clientes', [ClienteController::class, 'index']);
            $grupoProtegido->post('/clientes', [ClienteController::class, 'store']);
            $grupoProtegido->get('/clientes/{id:[0-9]+}', [ClienteController::class, 'show']);
            $grupoProtegido->put('/clientes/{id:[0-9]+}', [ClienteController::class, 'update']);
            $grupoProtegido->delete('/clientes/{id:[0-9]+}', [ClienteController::class, 'destroy']);
            $grupoProtegido->get('/clientes/{id:[0-9]+}/historial', [ClienteController::class, 'historial']);

            $grupoProtegido->get('/reservas', [ReservaController::class, 'index']);
            $grupoProtegido->post('/reservas', [ReservaController::class, 'store']);
            $grupoProtegido->get('/reservas/{id:[0-9]+}', [ReservaController::class, 'show']);
            $grupoProtegido->post('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'devolver']);
            $grupoProtegido->post('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'cancelar']);
        })->add(AuthMiddleware::class);
    });
};
