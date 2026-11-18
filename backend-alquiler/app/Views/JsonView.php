<?php

declare(strict_types=1);

namespace App\Views;

use Psr\Http\Message\ResponseInterface;

/**
 * Construye respuestas JSON con el formato estandar de la API:
 * { "ok": bool, "mensaje": string, "data": mixed }.
 */
final class JsonView
{
    /**
     * Escribe la respuesta estandar en el cuerpo del response.
     *
     * @param mixed $datos
     */
    public static function render(
        ResponseInterface $response,
        bool $exitoso,
        string $mensaje,
        $datos = null,
        int $codigoEstado = 200
    ): ResponseInterface {
        $cuerpoRespuesta = [
            'ok' => $exitoso,
            'mensaje' => $mensaje,
            'data' => $datos,
        ];

        $response->getBody()->write((string) json_encode($cuerpoRespuesta, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($codigoEstado);
    }
}
