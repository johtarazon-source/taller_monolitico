<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Exceptions\ValidationException;
use App\Services\DisponibilidadService;
use App\Services\VehiculoService;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Consulta de vehiculos disponibles en un periodo.
 */
final class DisponibilidadController extends WebController
{
    public function __construct(HtmlView $vista, private VehiculoService $servicioVehiculos)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parametrosConsulta = $request->getQueryParams();
        $fechaInicio = $parametrosConsulta['fecha_inicio'] ?? DisponibilidadService::hoy();
        $fechaFin = $parametrosConsulta['fecha_fin'] ?? $fechaInicio;
        $resultadoConsulta = null;
        $errores = [];

        try {
            $resultadoConsulta = $this->servicioVehiculos->disponibles($fechaInicio, $fechaFin);
        } catch (ValidationException $excepcionValidacion) {
            $errores = $excepcionValidacion->obtenerErrores() ?? [];
        }

        return $this->render($response, 'disponibilidad', [
            'titulo' => 'Disponibilidad',
            'seccion' => 'disponibilidad',
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'resultadoConsulta' => $resultadoConsulta,
            'errores' => $errores,
        ], $errores === [] ? 200 : 422);
    }
}
