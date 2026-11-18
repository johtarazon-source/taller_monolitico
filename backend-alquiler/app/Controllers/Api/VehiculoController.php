<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\VehiculoService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Endpoints de la flota de vehiculos, su disponibilidad e historial.
 */
final class VehiculoController extends BaseController
{
    public function __construct(private VehiculoService $vehiculos)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($response, true, 'Vehículos obtenidos.', $this->vehiculos->listar($request->getQueryParams()));
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Vehículo obtenido.', $this->vehiculos->obtener((int) $args['id']));
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $vehiculo = $this->vehiculos->crear($this->cuerpo($request));

        return $this->json($response, true, 'Vehículo registrado correctamente.', $vehiculo, 201);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculo = $this->vehiculos->actualizar((int) $args['id'], $this->cuerpo($request));

        return $this->json($response, true, 'Vehículo actualizado correctamente.', $vehiculo);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->vehiculos->eliminar((int) $args['id']);

        return $this->json($response, true, 'Vehículo eliminado correctamente.', null);
    }

    public function disponibles(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $resultado = $this->vehiculos->disponibles($query['fecha_inicio'] ?? null, $query['fecha_fin'] ?? null);

        return $this->json($response, true, 'Vehículos disponibles obtenidos.', $resultado);
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Historial del vehículo obtenido.', $this->vehiculos->historial((int) $args['id']));
    }
}
