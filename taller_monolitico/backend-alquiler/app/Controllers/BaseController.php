<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\UnauthorizedException;
use App\Services\AuthService;
use App\Views\JsonView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controlador base. Expone el helper de respuesta JSON estandar y la
 * validacion de autenticacion que usan las rutas protegidas.
 */
abstract class BaseController
{
    public function __construct(protected AuthService $authService)
    {
    }

    protected function json(
        ResponseInterface $response,
        bool $ok,
        string $mensaje,
        $data = null,
        int $status = 200
    ): ResponseInterface {
        return JsonView::render($response, $ok, $mensaje, $data, $status);
    }

    /**
     * Exige un token Bearer valido, activo y no expirado.
     *
     * @throws UnauthorizedException si falta el token o no es valido
     */
    protected function requireAuth(ServerRequestInterface $request): void
    {
        if (!$this->authService->validarToken($this->extraerToken($request))) {
            throw new UnauthorizedException('Token invalido o expirado.');
        }
    }

    protected function extraerToken(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('Authorization');

        if (!str_starts_with($header, 'Bearer ')) {
            return '';
        }

        return substr($header, 7);
    }
}
