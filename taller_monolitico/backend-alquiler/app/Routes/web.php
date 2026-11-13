<?php

declare(strict_types=1);

use App\Controllers\Web\AuthController;
use App\Controllers\Web\ClienteController;
use App\Controllers\Web\DisponibilidadController;
use App\Controllers\Web\PanelController;
use App\Controllers\Web\ReservaController;
use App\Controllers\Web\VehiculoController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SesionWebMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * Paginas HTML. Los formularios solo usan GET (consultas) y POST (cambios);
 * cada accion destructiva tiene una pagina de confirmacion previa.
 */
return function (App $app): void {
    $app->group('', function (RouteCollectorProxy $web): void {
        $web->get('/login', [AuthController::class, 'mostrarLogin']);
        $web->post('/login', [AuthController::class, 'login']);

        $web->group('', function (RouteCollectorProxy $g): void {
            $g->post('/logout', [AuthController::class, 'logout']);

            $g->get('/', [PanelController::class, 'index']);

            $g->get('/vehiculos', [VehiculoController::class, 'index']);
            $g->get('/vehiculos/nuevo', [VehiculoController::class, 'crear']);
            $g->post('/vehiculos', [VehiculoController::class, 'guardar']);
            $g->get('/vehiculos/{id:[0-9]+}/editar', [VehiculoController::class, 'editar']);
            $g->post('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'actualizar']);
            $g->get('/vehiculos/{id:[0-9]+}/eliminar', [VehiculoController::class, 'confirmarEliminar']);
            $g->post('/vehiculos/{id:[0-9]+}/eliminar', [VehiculoController::class, 'eliminar']);
            $g->get('/vehiculos/{id:[0-9]+}/historial', [VehiculoController::class, 'historial']);

            $g->get('/clientes', [ClienteController::class, 'index']);
            $g->get('/clientes/nuevo', [ClienteController::class, 'crear']);
            $g->post('/clientes', [ClienteController::class, 'guardar']);
            $g->get('/clientes/{id:[0-9]+}/editar', [ClienteController::class, 'editar']);
            $g->post('/clientes/{id:[0-9]+}', [ClienteController::class, 'actualizar']);
            $g->get('/clientes/{id:[0-9]+}/eliminar', [ClienteController::class, 'confirmarEliminar']);
            $g->post('/clientes/{id:[0-9]+}/eliminar', [ClienteController::class, 'eliminar']);
            $g->get('/clientes/{id:[0-9]+}/historial', [ClienteController::class, 'historial']);

            $g->get('/reservas', [ReservaController::class, 'index']);
            $g->get('/reservas/nueva', [ReservaController::class, 'nueva']);
            $g->post('/reservas', [ReservaController::class, 'guardar']);
            $g->get('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'confirmarDevolucion']);
            $g->post('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'devolver']);
            $g->get('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'confirmarCancelacion']);
            $g->post('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'cancelar']);

            $g->get('/disponibilidad', [DisponibilidadController::class, 'index']);
        })->add(SesionWebMiddleware::class);
    })->add(CsrfMiddleware::class);
};
