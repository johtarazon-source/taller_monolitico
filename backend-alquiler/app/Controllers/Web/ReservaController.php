<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\ClienteService;
use App\Services\DisponibilidadService;
use App\Services\ReservaService;
use App\Services\VehiculoService;
use App\Views\Formato;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Paginas de reservas: listado, nueva reserva, devolucion y cancelacion.
 */
final class ReservaController extends WebController
{
    public function __construct(
        HtmlView $vista,
        private ReservaService $reservas,
        private ClienteService $clientes,
        private VehiculoService $vehiculos
    ) {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Por defecto se muestran las activas; "todas" quita el filtro.
        $estado = (string) ($request->getQueryParams()['estado'] ?? 'activa');

        return $this->render($response, 'reservas/index', [
            'titulo' => 'Reservas',
            'seccion' => 'reservas',
            'estado' => $estado,
            'reservas' => $this->reservas->listar(['estado' => $estado === 'todas' ? '' : $estado]),
        ]);
    }

    /**
     * Paso 1: elegir fechas (formulario GET). Paso 2: con fechas validas se
     * listan solo los vehiculos disponibles y se elige cliente y vehiculo.
     */
    public function nueva(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();

        return $this->formularioReserva($response, [
            'fecha_inicio' => $query['fecha_inicio'] ?? DisponibilidadService::hoy(),
            'fecha_fin' => $query['fecha_fin'] ?? null,
            'vehiculo_id' => $query['vehiculo_id'] ?? null,
            'cliente_id' => $query['cliente_id'] ?? null,
        ], isset($query['fecha_fin']));
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $datos = $this->formulario($request);

        try {
            $this->reservas->crear($datos);
        } catch (ValidationException $e) {
            return $this->formularioReserva($response, $datos, true, $e->getErrores() ?? [], 422);
        } catch (AppException $e) {
            return $this->formularioReserva($response, $datos, true, ['general' => $e->getMessage()], $e->getStatusCode());
        }

        $this->exito('Reserva registrada correctamente.');

        return $this->redirigir($response, '/reservas');
    }

    public function confirmarDevolucion(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $reserva = $this->reservas->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Registrar devolución',
            'seccion' => 'reservas',
            'mensaje' => "{$reserva->cliente->nombre} devuelve el " . Formato::vehiculo($reserva->vehiculo) . '.',
            'accion' => "/reservas/{$reserva->id}/devolucion",
            'volver' => '/reservas',
            'textoBoton' => 'Registrar devolución',
            'peligro' => false,
            'opcionMantenimiento' => true,
        ]);
    }

    public function devolver(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $mantenimiento = ($this->formulario($request)['mantenimiento'] ?? '') === '1';

        try {
            $this->reservas->devolver((int) $args['id'], $mantenimiento);
            $this->exito('Devolución registrada correctamente.');
        } catch (AppException $e) {
            $this->error($e->getMessage());
        }

        return $this->redirigir($response, '/reservas');
    }

    public function confirmarCancelacion(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $reserva = $this->reservas->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Cancelar reserva',
            'seccion' => 'reservas',
            'mensaje' => "¿Cancelar la reserva #{$reserva->id} de {$reserva->cliente->nombre}? El vehículo quedará libre.",
            'accion' => "/reservas/{$reserva->id}/cancelacion",
            'volver' => '/reservas',
            'textoBoton' => 'Cancelar reserva',
            'peligro' => true,
        ]);
    }

    public function cancelar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->reservas->cancelar((int) $args['id']);
            $this->exito('Reserva cancelada correctamente.');
        } catch (AppException $e) {
            $this->error($e->getMessage());
        }

        return $this->redirigir($response, '/reservas');
    }

    /**
     * @param array<string, mixed> $datos
     * @param array<string, string> $errores
     */
    private function formularioReserva(
        ResponseInterface $response,
        array $datos,
        bool $buscarVehiculos,
        array $errores = [],
        int $status = 200
    ): ResponseInterface {
        $disponibles = null;

        if ($buscarVehiculos) {
            try {
                $disponibles = $this->vehiculos->disponibles(
                    $datos['fecha_inicio'] ?? null,
                    $datos['fecha_fin'] ?? null
                )['vehiculos'];
            } catch (ValidationException $e) {
                $errores += $e->getErrores() ?? [];
                $status = 422;
            }
        }

        return $this->render($response, 'reservas/nueva', [
            'titulo' => 'Nueva reserva',
            'seccion' => 'reservas',
            'datos' => $datos,
            'errores' => $errores,
            'clientes' => $this->clientes->listar(null),
            'disponibles' => $disponibles,
        ], $status);
    }
}
