<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\ClienteService;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Paginas de clientes: listado, alta, edicion, eliminacion e historial.
 */
final class ClienteController extends WebController
{
    public function __construct(HtmlView $vista, private ClienteService $clientes)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $buscar = (string) ($request->getQueryParams()['buscar'] ?? '');

        return $this->render($response, 'clientes/index', [
            'titulo' => 'Clientes',
            'seccion' => 'clientes',
            'buscar' => $buscar,
            'clientes' => $this->clientes->listar($buscar),
        ]);
    }

    public function crear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->formularioCliente($response, []);
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $datos = $this->formulario($request);

        try {
            $this->clientes->crear($datos);
        } catch (ValidationException $e) {
            return $this->formularioCliente($response, $datos, null, $e->getErrores() ?? [], 422);
        }

        $this->exito('Cliente registrado correctamente.');

        return $this->redirigir($response, '/clientes');
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cliente = $this->clientes->obtener((int) $args['id']);

        return $this->formularioCliente($response, $cliente->toArray(), $cliente->id);
    }

    public function actualizar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $datos = $this->formulario($request);

        try {
            $this->clientes->actualizar($id, $datos);
        } catch (ValidationException $e) {
            return $this->formularioCliente($response, $datos, $id, $e->getErrores() ?? [], 422);
        }

        $this->exito('Cliente actualizado correctamente.');

        return $this->redirigir($response, '/clientes');
    }

    public function confirmarEliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cliente = $this->clientes->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Eliminar cliente',
            'seccion' => 'clientes',
            'mensaje' => "¿Eliminar a {$cliente->nombre}? Esta acción no se puede deshacer.",
            'accion' => "/clientes/{$cliente->id}/eliminar",
            'volver' => '/clientes',
            'textoBoton' => 'Eliminar',
            'peligro' => true,
        ]);
    }

    public function eliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->clientes->eliminar((int) $args['id']);
            $this->exito('Cliente eliminado correctamente.');
        } catch (AppException $e) {
            $this->error($e->getMessage());
        }

        return $this->redirigir($response, '/clientes');
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];

        return $this->render($response, 'historial', [
            'titulo' => 'Historial del cliente',
            'seccion' => 'clientes',
            'tipo' => 'cliente',
            'entidad' => $this->clientes->obtener($id),
            'reservas' => $this->clientes->historial($id),
        ]);
    }

    /**
     * @param array<string, mixed> $datos
     * @param array<string, string> $errores
     */
    private function formularioCliente(
        ResponseInterface $response,
        array $datos,
        ?int $id = null,
        array $errores = [],
        int $status = 200
    ): ResponseInterface {
        return $this->render($response, 'clientes/formulario', [
            'titulo' => $id === null ? 'Nuevo cliente' : 'Editar cliente',
            'seccion' => 'clientes',
            'id' => $id,
            'datos' => $datos,
            'errores' => $errores,
        ], $status);
    }
}
