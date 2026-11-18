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
return function (App $aplicacion): void {
    $aplicacion->group('', function (RouteCollectorProxy $grupoWeb): void {
        $grupoWeb->get('/login', [AuthController::class, 'mostrarLogin']);
        $grupoWeb->post('/login', [AuthController::class, 'login']);

        $grupoWeb->group('', function (RouteCollectorProxy $grupoProtegido): void {
            $grupoProtegido->post('/logout', [AuthController::class, 'logout']);

            $grupoProtegido->get('/', [PanelController::class, 'index']);

            $grupoProtegido->get('/vehiculos', [VehiculoController::class, 'index']);
            $grupoProtegido->get('/vehiculos/nuevo', [VehiculoController::class, 'crear']);
            $grupoProtegido->post('/vehiculos', [VehiculoController::class, 'guardar']);
            $grupoProtegido->get('/vehiculos/{id:[0-9]+}/editar', [VehiculoController::class, 'editar']);
            $grupoProtegido->post('/vehiculos/{id:[0-9]+}', [VehiculoController::class, 'actualizar']);
            $grupoProtegido->get('/vehiculos/{id:[0-9]+}/eliminar', [VehiculoController::class, 'confirmarEliminar']);
            $grupoProtegido->post('/vehiculos/{id:[0-9]+}/eliminar', [VehiculoController::class, 'eliminar']);
            $grupoProtegido->get('/vehiculos/{id:[0-9]+}/historial', [VehiculoController::class, 'historial']);

            $grupoProtegido->get('/clientes', [ClienteController::class, 'index']);
            $grupoProtegido->get('/clientes/nuevo', [ClienteController::class, 'crear']);
            $grupoProtegido->post('/clientes', [ClienteController::class, 'guardar']);
            $grupoProtegido->get('/clientes/{id:[0-9]+}/editar', [ClienteController::class, 'editar']);
            $grupoProtegido->post('/clientes/{id:[0-9]+}', [ClienteController::class, 'actualizar']);
            $grupoProtegido->get('/clientes/{id:[0-9]+}/eliminar', [ClienteController::class, 'confirmarEliminar']);
            $grupoProtegido->post('/clientes/{id:[0-9]+}/eliminar', [ClienteController::class, 'eliminar']);
            $grupoProtegido->get('/clientes/{id:[0-9]+}/historial', [ClienteController::class, 'historial']);

            $grupoProtegido->get('/reservas', [ReservaController::class, 'index']);
            $grupoProtegido->get('/reservas/nueva', [ReservaController::class, 'nueva']);
            $grupoProtegido->post('/reservas', [ReservaController::class, 'guardar']);
            $grupoProtegido->get('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'confirmarDevolucion']);
            $grupoProtegido->post('/reservas/{id:[0-9]+}/devolucion', [ReservaController::class, 'devolver']);
            $grupoProtegido->get('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'confirmarCancelacion']);
            $grupoProtegido->post('/reservas/{id:[0-9]+}/cancelacion', [ReservaController::class, 'cancelar']);

            $grupoProtegido->get('/disponibilidad', [DisponibilidadController::class, 'index']);
        })->add(SesionWebMiddleware::class);
    })->add(CsrfMiddleware::class);
};
