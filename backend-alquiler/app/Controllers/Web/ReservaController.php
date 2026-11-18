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
        private ReservaService $servicioReservas,
        private ClienteService $servicioClientes,
        private VehiculoService $servicioVehiculos
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
            'reservas' => $this->servicioReservas->listar(['estado' => $estado === 'todas' ? '' : $estado]),
        ]);
    }

    /**
     * Paso 1: elegir fechas (formulario GET). Paso 2: con fechas validas se
     * listan solo los vehiculos disponibles y se elige cliente y vehiculo.
     */
    public function nueva(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parametrosConsulta = $request->getQueryParams();

        return $this->formularioReserva($response, [
            'fecha_inicio' => $parametrosConsulta['fecha_inicio'] ?? DisponibilidadService::hoy(),
            'fecha_fin' => $parametrosConsulta['fecha_fin'] ?? null,
            'vehiculo_id' => $parametrosConsulta['vehiculo_id'] ?? null,
            'cliente_id' => $parametrosConsulta['cliente_id'] ?? null,
        ], isset($parametrosConsulta['fecha_fin']));
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $camposFormulario = $this->formulario($request);

        try {
            $this->servicioReservas->crear($camposFormulario);
        } catch (ValidationException $excepcionValidacion) {
            return $this->formularioReserva($response, $camposFormulario, true, $excepcionValidacion->obtenerErrores() ?? [], 422);
        } catch (AppException $excepcionAplicacion) {
            return $this->formularioReserva($response, $camposFormulario, true, ['general' => $excepcionAplicacion->getMessage()], $excepcionAplicacion->obtenerCodigoEstado());
        }

        $this->exito('Reserva registrada correctamente.');

        return $this->redirigir($response, '/reservas');
    }

    public function confirmarDevolucion(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $reserva = $this->servicioReservas->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Registrar devolución',
            'seccion' => 'reservas',
            'mensaje' => "{$reserva->cliente->nombre} devuelve el " . Formato::vehiculo($reserva->vehiculo) . '.',
            'rutaAccion' => "/reservas/{$reserva->id}/devolucion",
            'rutaVolver' => '/reservas',
            'textoBoton' => 'Registrar devolución',
            'esPeligrosa' => false,
            'opcionMantenimiento' => true,
        ]);
    }

    public function devolver(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $enviarAMantenimiento = ($this->formulario($request)['mantenimiento'] ?? '') === '1';

        try {
            $this->servicioReservas->devolver((int) $args['id'], $enviarAMantenimiento);
            $this->exito('Devolución registrada correctamente.');
        } catch (AppException $excepcionAplicacion) {
            $this->error($excepcionAplicacion->getMessage());
        }

        return $this->redirigir($response, '/reservas');
    }

    public function confirmarCancelacion(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $reserva = $this->servicioReservas->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Cancelar reserva',
            'seccion' => 'reservas',
            'mensaje' => "¿Cancelar la reserva #{$reserva->id} de {$reserva->cliente->nombre}? El vehículo quedará libre.",
            'rutaAccion' => "/reservas/{$reserva->id}/cancelacion",
            'rutaVolver' => '/reservas',
            'textoBoton' => 'Cancelar reserva',
            'esPeligrosa' => true,
        ]);
    }

    public function cancelar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->servicioReservas->cancelar((int) $args['id']);
            $this->exito('Reserva cancelada correctamente.');
        } catch (AppException $excepcionAplicacion) {
            $this->error($excepcionAplicacion->getMessage());
        }

        return $this->redirigir($response, '/reservas');
    }

    /**
     * @param array<string, mixed> $camposFormulario
     * @param array<string, string> $errores
     */
    private function formularioReserva(
        ResponseInterface $response,
        array $camposFormulario,
        bool $buscarVehiculos,
        array $errores = [],
        int $codigoEstado = 200
    ): ResponseInterface {
        $vehiculosDisponibles = null;

        if ($buscarVehiculos) {
            try {
                $vehiculosDisponibles = $this->servicioVehiculos->disponibles(
                    $camposFormulario['fecha_inicio'] ?? null,
                    $camposFormulario['fecha_fin'] ?? null
                )['vehiculos'];
            } catch (ValidationException $excepcionValidacion) {
                $errores += $excepcionValidacion->obtenerErrores() ?? [];
                $codigoEstado = 422;
            }
        }

        return $this->render($response, 'reservas/nueva', [
            'titulo' => 'Nueva reserva',
            'seccion' => 'reservas',
            'datosFormulario' => $camposFormulario,
            'errores' => $errores,
            'clientes' => $this->servicioClientes->listar(null),
            'vehiculosDisponibles' => $vehiculosDisponibles,
        ], $codigoEstado);
    }
}
