<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Support\Sesion;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base de los controladores que devuelven paginas HTML.
 */
abstract class WebController
{
    public function __construct(protected HtmlView $vista)
    {
    }

    /**
     * @param array<string, mixed> $datosVista
     */
    protected function render(ResponseInterface $response, string $plantilla, array $datosVista = [], int $codigoEstado = 200): ResponseInterface
    {
        return $this->vista->render($response, $plantilla, $datosVista, $codigoEstado);
    }

    /**
     * Redirige tras un POST (patron Post/Redirect/Get) para que recargar
     * la pagina no reenvie el formulario.
     *
     * @param array<string, scalar|null> $parametrosConsulta
     */
    protected function redirigir(ResponseInterface $response, string $ruta, array $parametrosConsulta = []): ResponseInterface
    {
        return $response->withHeader('Location', $this->vista->url($ruta, $parametrosConsulta))->withStatus(303);
    }

    protected function exito(string $mensaje): void
    {
        Sesion::mensajeTemporal('exito', $mensaje);
    }

    protected function error(string $mensaje): void
    {
        Sesion::mensajeTemporal('error', $mensaje);
    }

    /**
     * Datos del formulario sin el token CSRF.
     *
     * @return array<string, mixed>
     */
    protected function formulario(ServerRequestInterface $request): array
    {
        $camposFormulario = (array) $request->getParsedBody();
        unset($camposFormulario['_csrf']);

        return $camposFormulario;
    }
}
