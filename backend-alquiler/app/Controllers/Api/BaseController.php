<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Exceptions\AppException;
use App\Views\JsonView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controlador base. Expone el helper de respuesta JSON estandar.
 * La autenticacion de las rutas protegidas la hace AuthMiddleware.
 */
abstract class BaseController
{
    protected function json(
        ResponseInterface $response,
        bool $exitoso,
        string $mensaje,
        $datos = null,
        int $codigoEstado = 200
    ): ResponseInterface {
        return JsonView::render($response, $exitoso, $mensaje, $datos, $codigoEstado);
    }

    /**
     * @return array<string, mixed>
     */
    protected function cuerpo(ServerRequestInterface $request): array
    {
        $cuerpoInterpretado = $request->getParsedBody();

        // Hay contenido pero no se pudo interpretar (JSON mal formado o mal codificado).
        if ($cuerpoInterpretado === null && trim((string) $request->getBody()) !== '') {
            throw new AppException('El cuerpo de la solicitud no es un JSON válido (UTF-8).', 400);
        }

        return (array) $cuerpoInterpretado;
    }
}
