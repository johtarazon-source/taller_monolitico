<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Services\AuthService;
use App\Support\Sesion;
use App\Views\HtmlView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Inicio y cierre de sesion de la interfaz web (sesion PHP).
 */
final class AuthController extends WebController
{
    public function __construct(HtmlView $vista, private AuthService $auth)
    {
        parent::__construct($vista);
    }

    public function mostrarLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (Sesion::usuario() !== null) {
            return $this->redirigir($response, '/');
        }

        return $this->render($response, 'login', ['titulo' => 'Iniciar sesión']);
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $datos = $this->formulario($request);
        $usuario = trim((string) ($datos['usuario'] ?? ''));
        $password = (string) ($datos['password'] ?? '');

        if ($usuario === '' || $password === '' || !$this->auth->credencialesValidas($usuario, $password)) {
            $mensaje = $usuario === '' || $password === ''
                ? 'Usuario y contraseña son obligatorios.'
                : 'Usuario o contraseña incorrectos.';

            return $this->render($response, 'login', [
                'titulo' => 'Iniciar sesión',
                'error' => $mensaje,
                'usuario' => $usuario,
            ], 401);
        }

        Sesion::autenticar($usuario);

        return $this->redirigir($response, '/');
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        Sesion::cerrar();

        return $this->redirigir($response, '/login');
    }
}
