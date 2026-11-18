<?php

declare(strict_types=1);

use App\Services\AuthService;
use App\Views\HtmlView;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * Construye el contenedor de inyeccion de dependencias de la aplicacion.
 * Aqui solo se registra lo que necesita configuracion (valores del .env o la
 * carpeta base); servicios, controladores y middleware se resuelven por autowiring.
 */
function construirContenedor(string $rutaBase): Container
{
    $contenedor = new Container();

    $contenedor->set(AuthService::class, function (): AuthService {
        return new AuthService(
            __DIR__ . '/../../storage/sessions.json',
            $_ENV['ADMIN_USER'] ?? '',
            $_ENV['ADMIN_PASSWORD_HASH'] ?? '',
            (int) ($_ENV['APP_TOKEN_TTL_MINUTES'] ?? 120)
        );
    });

    $contenedor->set(HtmlView::class, function () use ($rutaBase): HtmlView {
        return new HtmlView(__DIR__ . '/../Views/web', $rutaBase);
    });

    $contenedor->set(ResponseFactoryInterface::class, function (): ResponseFactoryInterface {
        return new ResponseFactory();
    });

    return $contenedor;
}
