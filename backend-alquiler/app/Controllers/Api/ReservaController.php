<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\ReservaService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Endpoints de reservas, devoluciones y cancelaciones.
 */
final class ReservaController extends BaseController
{
    public function __construct(private ReservaService $servicioReservas)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($response, true, 'Reservas obtenidas.', $this->servicioReservas->listar($request->getQueryParams()));
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Reserva obtenida.', $this->servicioReservas->obtener((int) $args['id']));
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $reserva = $this->servicioReservas->crear($this->cuerpo($request));

        return $this->json($response, true, 'Reserva registrada correctamente.', $reserva, 201);
    }

    public function devolver(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $enviarAMantenimiento = filter_var($this->cuerpo($request)['mantenimiento'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $reserva = $this->servicioReservas->devolver((int) $args['id'], $enviarAMantenimiento);

        return $this->json($response, true, 'Devolución registrada correctamente.', $reserva);
    }

    public function cancelar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Reserva cancelada correctamente.', $this->servicioReservas->cancelar((int) $args['id']));
    }
}
