<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Services\ReporteService;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Pagina principal con el resumen de la flota.
 */
final class PanelController extends WebController
{
    public function __construct(HtmlView $vista, private ReporteService $reportes)
    {
        parent::__construct($vista);
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->render($response, 'panel', [
            'titulo' => 'Panel general',
            'seccion' => 'panel',
            'resumen' => $this->reportes->resumen(),
        ]);
    }
}
