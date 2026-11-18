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
    public function __construct(private ReservaService $reservas)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($response, true, 'Reservas obtenidas.', $this->reservas->listar($request->getQueryParams()));
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Reserva obtenida.', $this->reservas->obtener((int) $args['id']));
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $reserva = $this->reservas->crear($this->cuerpo($request));

        return $this->json($response, true, 'Reserva registrada correctamente.', $reserva, 201);
    }

    public function devolver(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $mantenimiento = filter_var($this->cuerpo($request)['mantenimiento'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $reserva = $this->reservas->devolver((int) $args['id'], $mantenimiento);

        return $this->json($response, true, 'Devolución registrada correctamente.', $reserva);
    }

    public function cancelar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Reserva cancelada correctamente.', $this->reservas->cancelar((int) $args['id']));
    }
}
