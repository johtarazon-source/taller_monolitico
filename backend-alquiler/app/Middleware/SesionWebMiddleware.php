<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Support\Sesion;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Protege las paginas web: sin sesion iniciada redirige al login.
 */
final class SesionWebMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseFactoryInterface $respuestas, private HtmlView $vista)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Sesion::usuario() === null) {
            return $this->respuestas->createResponse(302)->withHeader('Location', $this->vista->url('/login'));
        }

        return $handler->handle($request);
    }
}
