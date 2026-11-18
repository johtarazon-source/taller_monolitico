<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Exceptions\AppException;
use App\Exceptions\ValidationException;
use App\Services\VehiculoService;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Paginas de la flota: listado, alta, edicion, eliminacion e historial.
 */
final class VehiculoController extends WebController
{
    public function __construct(HtmlView $vista, private VehiculoService $vehiculos)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $filtros = $request->getQueryParams();

        return $this->render($response, 'vehiculos/index', [
            'titulo' => 'Vehículos',
            'seccion' => 'vehiculos',
            'filtros' => $filtros,
            'vehiculos' => $this->vehiculos->listar($filtros),
        ]);
    }

    public function crear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->formularioVehiculo($response, []);
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $datos = $this->formulario($request);

        try {
            $this->vehiculos->crear($datos);
        } catch (ValidationException $e) {
            return $this->formularioVehiculo($response, $datos, null, $e->getErrores() ?? [], 422);
        }

        $this->exito('Vehículo registrado correctamente.');

        return $this->redirigir($response, '/vehiculos');
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculo = $this->vehiculos->obtener((int) $args['id']);

        return $this->formularioVehiculo($response, $vehiculo->toArray(), $vehiculo->id, [], 200, $vehiculo->estado);
    }

    public function actualizar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $datos = $this->formulario($request);

        try {
            $this->vehiculos->actualizar($id, $datos);
        } catch (ValidationException $e) {
            $estadoActual = $this->vehiculos->obtener($id)->estado;

            return $this->formularioVehiculo($response, $datos, $id, $e->getErrores() ?? [], 422, $estadoActual);
        } catch (AppException $e) {
            $this->error($e->getMessage());

            return $this->redirigir($response, "/vehiculos/{$id}/editar");
        }

        $this->exito('Vehículo actualizado correctamente.');

        return $this->redirigir($response, '/vehiculos');
    }

    public function confirmarEliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculo = $this->vehiculos->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Eliminar vehículo',
            'seccion' => 'vehiculos',
            'mensaje' => "¿Eliminar {$vehiculo->marca} {$vehiculo->modelo}? Esta acción no se puede deshacer.",
            'accion' => "/vehiculos/{$vehiculo->id}/eliminar",
            'volver' => '/vehiculos',
            'textoBoton' => 'Eliminar',
            'peligro' => true,
        ]);
    }

    public function eliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->vehiculos->eliminar((int) $args['id']);
            $this->exito('Vehículo eliminado correctamente.');
        } catch (AppException $e) {
            $this->error($e->getMessage());
        }

        return $this->redirigir($response, '/vehiculos');
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];

        return $this->render($response, 'historial', [
            'titulo' => 'Historial del vehículo',
            'seccion' => 'vehiculos',
            'tipo' => 'vehiculo',
            'entidad' => $this->vehiculos->obtener($id),
            'reservas' => $this->vehiculos->historial($id),
        ]);
    }

    /**
     * @param array<string, mixed> $datos
     * @param array<string, string> $errores
     */
    private function formularioVehiculo(
        ResponseInterface $response,
        array $datos,
        ?int $id = null,
        array $errores = [],
        int $status = 200,
        ?string $estadoActual = null
    ): ResponseInterface {
        return $this->render($response, 'vehiculos/formulario', [
            'titulo' => $id === null ? 'Nuevo vehículo' : 'Editar vehículo',
            'seccion' => 'vehiculos',
            'id' => $id,
            'datos' => $datos,
            'errores' => $errores,
            'estadoActual' => $estadoActual,
        ], $status);
    }
}
