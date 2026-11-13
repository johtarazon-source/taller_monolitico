<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\AppException;
use App\Support\Sesion;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Rechaza los formularios POST de la web que no traen el token CSRF de la sesion.
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            $token = (string) (((array) $request->getParsedBody())['_csrf'] ?? '');

            if (!Sesion::csrfValido($token)) {
                throw new AppException('El formulario expiró. Vuelva a cargar la página e inténtelo de nuevo.', 403);
            }
        }

        return $handler->handle($request);
    }
}
