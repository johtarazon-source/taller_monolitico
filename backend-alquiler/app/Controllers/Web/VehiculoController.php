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
    public function __construct(HtmlView $vista, private VehiculoService $servicioVehiculos)
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
            'vehiculos' => $this->servicioVehiculos->listar($filtros),
        ]);
    }

    public function crear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->formularioVehiculo($response, []);
    }

    public function guardar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $camposFormulario = $this->formulario($request);

        try {
            $this->servicioVehiculos->crear($camposFormulario);
        } catch (ValidationException $excepcionValidacion) {
            return $this->formularioVehiculo($response, $camposFormulario, null, $excepcionValidacion->obtenerErrores() ?? [], 422);
        }

        $this->exito('Vehículo registrado correctamente.');

        return $this->redirigir($response, '/vehiculos');
    }

    public function editar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculo = $this->servicioVehiculos->obtener((int) $args['id']);

        return $this->formularioVehiculo($response, $vehiculo->toArray(), $vehiculo->id, [], 200, $vehiculo->estado);
    }

    public function actualizar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculoId = (int) $args['id'];
        $camposFormulario = $this->formulario($request);

        try {
            $this->servicioVehiculos->actualizar($vehiculoId, $camposFormulario);
        } catch (ValidationException $excepcionValidacion) {
            $estadoActual = $this->servicioVehiculos->obtener($vehiculoId)->estado;

            return $this->formularioVehiculo($response, $camposFormulario, $vehiculoId, $excepcionValidacion->obtenerErrores() ?? [], 422, $estadoActual);
        } catch (AppException $excepcionAplicacion) {
            $this->error($excepcionAplicacion->getMessage());

            return $this->redirigir($response, "/vehiculos/{$vehiculoId}/editar");
        }

        $this->exito('Vehículo actualizado correctamente.');

        return $this->redirigir($response, '/vehiculos');
    }

    public function confirmarEliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculo = $this->servicioVehiculos->obtener((int) $args['id']);

        return $this->render($response, 'confirmar', [
            'titulo' => 'Eliminar vehículo',
            'seccion' => 'vehiculos',
            'mensaje' => "¿Eliminar {$vehiculo->marca} {$vehiculo->modelo}? Esta acción no se puede deshacer.",
            'rutaAccion' => "/vehiculos/{$vehiculo->id}/eliminar",
            'rutaVolver' => '/vehiculos',
            'textoBoton' => 'Eliminar',
            'esPeligrosa' => true,
        ]);
    }

    public function eliminar(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $this->servicioVehiculos->eliminar((int) $args['id']);
            $this->exito('Vehículo eliminado correctamente.');
        } catch (AppException $excepcionAplicacion) {
            $this->error($excepcionAplicacion->getMessage());
        }

        return $this->redirigir($response, '/vehiculos');
    }

    public function historial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $vehiculoId = (int) $args['id'];

        return $this->render($response, 'historial', [
            'titulo' => 'Historial del vehículo',
            'seccion' => 'vehiculos',
            'tipoHistorial' => 'vehiculo',
            'propietarioHistorial' => $this->servicioVehiculos->obtener($vehiculoId),
            'reservas' => $this->servicioVehiculos->historial($vehiculoId),
        ]);
    }

    /**
     * @param array<string, mixed> $camposFormulario
     * @param array<string, string> $errores
     */
    private function formularioVehiculo(
        ResponseInterface $response,
        array $camposFormulario,
        ?int $vehiculoId = null,
        array $errores = [],
        int $codigoEstado = 200,
        ?string $estadoActual = null
    ): ResponseInterface {
        return $this->render($response, 'vehiculos/formulario', [
            'titulo' => $vehiculoId === null ? 'Nuevo vehículo' : 'Editar vehículo',
            'seccion' => 'vehiculos',
            'vehiculoId' => $vehiculoId,
            'datosFormulario' => $camposFormulario,
            'errores' => $errores,
            'estadoActual' => $estadoActual,
        ], $codigoEstado);
    }
}
