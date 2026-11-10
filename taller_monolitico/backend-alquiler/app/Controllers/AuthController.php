<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Maneja el inicio y cierre de sesion del usuario administrador.
 */
final class AuthController extends BaseController
{
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cuerpo = (array) $request->getParsedBody();
        $usuario = trim((string) ($cuerpo['usuario'] ?? ''));
        $password = (string) ($cuerpo['password'] ?? '');

        if ($usuario === '' || $password === '') {
            return $this->json($response, false, 'Usuario y contraseña son obligatorios.', null, 422);
        }

        $token = $this->authService->login($usuario, $password);

        return $this->json($response, true, 'Inicio de sesión exitoso.', ['token' => $token]);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->requireAuth($request);

        $this->authService->logout($this->extraerToken($request));

        return $this->json($response, true, 'Sesión cerrada correctamente.', null);
    }
}
