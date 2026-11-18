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
    public function __construct(HtmlView $vista, private AuthService $servicioAutenticacion)
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
        $camposFormulario = $this->formulario($request);
        $usuario = trim((string) ($camposFormulario['usuario'] ?? ''));
        $contrasena = (string) ($camposFormulario['contrasena'] ?? '');

        if ($usuario === '' || $contrasena === '' || !$this->servicioAutenticacion->credencialesValidas($usuario, $contrasena)) {
            $mensaje = $usuario === '' || $contrasena === ''
                ? 'Usuario y contraseña son obligatorios.'
                : 'Usuario o contraseña incorrectos.';

            return $this->render($response, 'login', [
                'titulo' => 'Iniciar sesión',
                'mensajeError' => $mensaje,
                'nombreUsuario' => $usuario,
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
