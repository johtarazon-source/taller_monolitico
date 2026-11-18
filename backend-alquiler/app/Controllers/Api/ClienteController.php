<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\ClienteService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Endpoints de clientes y su historial de alquileres.
 */
final class ClienteController extends BaseController
{
    public function __construct(private ClienteService $servicioClientes)
    {
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $terminoBusqueda = $request->getQueryParams()['buscar'] ?? null;

        return $this->json($response, true, 'Clientes obtenidos.', $this->servicioClientes->listar($terminoBusqueda));
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Cliente obtenido.', $this->servicioClientes->obtener((int) $args['id']));
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cliente = $this->servicioClientes->crear($this->cuerpo($request));

        return $this->json($response, true, 'Cliente registrado correctamente.', $cliente, 201);
    }

    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cliente = $this->servicioClientes->actualizar((int) $args['id'], $this->cuerpo($request));

        return $this->json($response, true, 'Cliente actualizado correctamente.', $cliente);
    }

    public function destroy(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->servicioClientes->eliminar((int) $args['id']);

        return $this->json($response, true, 'Cliente eliminado correctamente.', null);
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return $this->json($response, true, 'Historial del cliente obtenido.', $this->servicioClientes->historial((int) $args['id']));
    }
}
