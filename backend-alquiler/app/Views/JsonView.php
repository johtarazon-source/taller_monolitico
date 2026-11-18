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
     * @param mixed $data
     */
    public static function render(
        ResponseInterface $response,
        bool $ok,
        string $mensaje,
        $data = null,
        int $status = 200
    ): ResponseInterface {
        $payload = [
            'ok' => $ok,
            'mensaje' => $mensaje,
            'data' => $data,
        ];

        $response->getBody()->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
