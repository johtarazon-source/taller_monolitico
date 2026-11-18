<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Middleware\AuthMiddleware;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maneja el inicio y cierre de sesion del usuario administrador.
 */
final class AuthController extends BaseController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cuerpo = $this->cuerpo($request);
        $usuario = trim((string) ($cuerpo['usuario'] ?? ''));
        $password = (string) ($cuerpo['password'] ?? '');

        if ($usuario === '' || $password === '') {
            return $this->json($response, false, 'Usuario y contraseña son obligatorios.', null, 422);
        }

        $token = $this->authService->login($usuario, $password);

        return $this->json($response, true, 'Inicio de sesión exitoso.', ['token' => $token, 'usuario' => $usuario]);
    }

    /**
     * Ruta protegida por AuthMiddleware: el token ya fue validado.
     */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->authService->logout((string) $request->getAttribute(AuthMiddleware::ATRIBUTO_TOKEN, ''));

        return $this->json($response, true, 'Sesión cerrada correctamente.', null);
    }
}
