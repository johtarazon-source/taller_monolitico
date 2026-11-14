<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Protege un grupo de rutas exigiendo un token Bearer valido.
 * Deja el token en el atributo "token" del request para los controladores.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public const ATRIBUTO_TOKEN = 'token';

    public function __construct(private AuthService $authService)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        $token = str_starts_with($header, 'Bearer ') ? substr($header, 7) : '';

        if (!$this->authService->validarToken($token)) {
            throw new UnauthorizedException('Token invalido o expirado.');
        }

        return $handler->handle($request->withAttribute(self::ATRIBUTO_TOKEN, $token));
    }
}
