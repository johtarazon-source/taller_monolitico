<?php

declare(strict_types=1);

namespace App\Views;

use App\Support\Sesion;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Renderiza plantillas PHP de app/Views/web dentro del layout comun.
 * Las plantillas se ejecutan en el contexto de esta clase, por eso pueden
 * usar $this->escapar(), $this->url(), $this->parcial(), etc.
 */
final class HtmlView
{
    public function __construct(private string $directorio, private string $rutaBase)
    {
    }

    /**
     * @param array<string, mixed> $variablesPlantilla
     */
    public function render(ResponseInterface $response, string $plantilla, array $variablesPlantilla = [], int $codigoEstado = 200): ResponseInterface
    {
        $contenido = $this->renderizar($plantilla, $variablesPlantilla);
        $contenidoHtml = $this->renderizar('layout', $variablesPlantilla + ['contenido' => $contenido]);

        $response->getBody()->write($contenidoHtml);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8')->withStatus($codigoEstado);
    }

    /**
     * Renderiza un fragmento reutilizable (archivos que empiezan con "_").
     *
     * @param array<string, mixed> $variablesPlantilla
     */
    public function parcial(string $plantilla, array $variablesPlantilla = []): string
    {
        return $this->renderizar($plantilla, $variablesPlantilla);
    }

    /** Escapa texto antes de imprimirlo en HTML. */
    public function escapar(mixed $valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * URL interna con la carpeta base de la aplicacion.
     *
     * @param array<string, scalar|null> $parametrosConsulta
     */
    public function url(string $ruta, array $parametrosConsulta = []): string
    {
        $parametrosConsulta = array_filter($parametrosConsulta, static fn ($valor) => $valor !== null && $valor !== '');
        $direccion = $this->rutaBase . $ruta;

        return $parametrosConsulta === [] ? $direccion : $direccion . '?' . http_build_query($parametrosConsulta);
    }

    /** Campo oculto con el token CSRF para formularios POST. */
    public function csrf(): string
    {
        return '<input type="hidden" name="_csrf" value="' . $this->escapar(Sesion::tokenCsrf()) . '">';
    }

    /**
     * Los nombres internos llevan prefijo "__" para que las variables que
     * recibe la plantilla (por ejemplo $datosFormulario) no choquen con las del metodo.
     *
     * @param array<string, mixed> $__variables
     */
    private function renderizar(string $__plantilla, array $__variables): string
    {
        $__archivo = $this->directorio . '/' . $__plantilla . '.php';

        if (!is_file($__archivo)) {
            throw new RuntimeException("No existe la plantilla {$__plantilla}.");
        }

        extract($__variables, EXTR_SKIP);
        ob_start();

        try {
            require $__archivo;
        } catch (\Throwable $excepcion) {
            ob_end_clean();
            throw $excepcion;
        }

        return (string) ob_get_clean();
    }
}
