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
    public function __construct(HtmlView $vista, private ClienteService $servicioClientes)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $terminoBusqueda = (string) ($request->getQueryParams()['buscar'] ?? '');

        return $this->render($response, 'clientes/index', [
            'titulo' => 'Clientes',
            'seccion' => 'clientes',
            'terminoBusqueda' => $terminoBusqueda,
            'clientes' => $this->servicioClientes->listar($terminoBusqueda),
        ]);
    }

    public function crear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->formularioCliente($response, []);
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $camposFormulario = $this->formulario($request);

        try {
            $this->servicioClientes->crear($camposFormulario);
        } catch (ValidationException $excepcionValidacion) {
            return $this->formularioCliente($response, $camposFormulario, null, $excepcionValidacion->obtenerErrores() ?? [], 422);
        }

        $this->exito('Cliente registrado correctamente.');

        return $this->redirigir($response, '/clientes');
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cliente = $this->servicioClientes->obtener((int) $args['id']);

        return $this->formularioCliente($response, $cliente->toArray(), $cliente->id);
    }

    public function actualizar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clienteId = (int) $args['id'];
        $camposFormulario = $this->formulario($request);

        try {
            $this->servicioClientes->actualizar($clienteId, $camposFormulario);
        } catch (ValidationException $excepcionValidacion) {
            return $this->formularioCliente($response, $camposFormulario, $clienteId, $excepcionValidacion->obtenerErrores() ?? [], 422);
        }

        $this->exito('Cliente actualizado correctamente.');

        return $this->redirigir($response, '/clientes');
    }

    public function confirmarEliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $cliente = $this->servicioClientes->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Eliminar cliente',
            'seccion' => 'clientes',
            'mensaje' => "¿Eliminar a {$cliente->nombre}? Esta acción no se puede deshacer.",
            'rutaAccion' => "/clientes/{$cliente->id}/eliminar",
            'rutaVolver' => '/clientes',
            'textoBoton' => 'Eliminar',
            'esPeligrosa' => true,
        ]);
    }

    public function eliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->servicioClientes->eliminar((int) $args['id']);
            $this->exito('Cliente eliminado correctamente.');
        } catch (AppException $excepcionAplicacion) {
            $this->error($excepcionAplicacion->getMessage());
        }

        return $this->redirigir($response, '/clientes');
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $clienteId = (int) $args['id'];

        return $this->render($response, 'historial', [
            'titulo' => 'Historial del cliente',
            'seccion' => 'clientes',
            'tipoHistorial' => 'cliente',
            'propietarioHistorial' => $this->servicioClientes->obtener($clienteId),
            'reservas' => $this->servicioClientes->historial($clienteId),
        ]);
    }

    /**
     * @param array<string, mixed> $camposFormulario
     * @param array<string, string> $errores
     */
    private function formularioCliente(
        ResponseInterface $response,
        array $camposFormulario,
        ?int $clienteId = null,
        array $errores = [],
        int $codigoEstado = 200
    ): ResponseInterface {
        return $this->render($response, 'clientes/formulario', [
            'titulo' => $clienteId === null ? 'Nuevo cliente' : 'Editar cliente',
            'seccion' => 'clientes',
            'clienteId' => $clienteId,
            'datosFormulario' => $camposFormulario,
            'errores' => $errores,
        ], $codigoEstado);
    }
}
