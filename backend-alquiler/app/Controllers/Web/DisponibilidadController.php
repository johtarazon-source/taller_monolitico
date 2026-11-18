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
    public function __construct(HtmlView $vista, private VehiculoService $vehiculos)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $inicio = $query['fecha_inicio'] ?? DisponibilidadService::hoy();
        $fin = $query['fecha_fin'] ?? $inicio;
        $resultado = null;
        $errores = [];

        try {
            $resultado = $this->vehiculos->disponibles($inicio, $fin);
        } catch (ValidationException $e) {
            $errores = $e->getErrores() ?? [];
        }

        return $this->render($response, 'disponibilidad', [
            'titulo' => 'Disponibilidad',
            'seccion' => 'disponibilidad',
            'inicio' => $inicio,
            'fin' => $fin,
            'resultado' => $resultado,
            'errores' => $errores,
        ], $errores === [] ? 200 : 422);
    }
}
