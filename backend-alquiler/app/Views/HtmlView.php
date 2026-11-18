<?php

declare(strict_types=1);

namespace App\Views;

use App\Support\Sesion;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Renderiza plantillas PHP de app/Views/web dentro del layout comun.
 * Las plantillas se ejecutan en el contexto de esta clase, por eso pueden
 * usar $this->e(), $this->url(), $this->parcial(), etc.
 */
final class HtmlView
{
    public function __construct(private string $directorio, private string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function render(ResponseInterface $response, string $plantilla, array $datos = [], int $status = 200): ResponseInterface
    {
        $contenido = $this->renderizar($plantilla, $datos);
        $html = $this->renderizar('layout', $datos + ['contenido' => $contenido]);

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8')->withStatus($status);
    }

    /**
     * Renderiza un fragmento reutilizable (archivos que empiezan con "_").
     *
     * @param array<string, mixed> $datos
     */
    public function parcial(string $plantilla, array $datos = []): string
    {
        return $this->renderizar($plantilla, $datos);
    }

    /** Escapa texto antes de imprimirlo en HTML. */
    public function e(mixed $valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * URL interna con la carpeta base de la aplicacion.
     *
     * @param array<string, scalar|null> $query
     */
    public function url(string $ruta, array $query = []): string
    {
        $query = array_filter($query, static fn ($valor) => $valor !== null && $valor !== '');
        $url = $this->basePath . $ruta;

        return $query === [] ? $url : $url . '?' . http_build_query($query);
    }

    /** Campo oculto con el token CSRF para formularios POST. */
    public function csrf(): string
    {
        return '<input type="hidden" name="_csrf" value="' . $this->e(Sesion::tokenCsrf()) . '">';
    }

    /**
     * Los nombres internos llevan prefijo "__" para que las variables que
     * recibe la plantilla (por ejemplo $datos) no choquen con las del metodo.
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
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }

        return (string) ob_get_clean();
    }
}
