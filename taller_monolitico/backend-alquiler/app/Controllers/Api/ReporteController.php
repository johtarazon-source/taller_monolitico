<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\ReporteService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Reportes basicos de flota y reservas.
 */
final class ReporteController extends BaseController
{
    public function __construct(private ReporteService $reportes)
    {
    }

    public function resumen(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->json($response, true, 'Resumen obtenido.', $this->reportes->resumen());
    }
}
