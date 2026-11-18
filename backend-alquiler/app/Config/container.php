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
function buildContainer(string $basePath): Container
{
    $container = new Container();

    $container->set(AuthService::class, function (): AuthService {
        return new AuthService(
            __DIR__ . '/../../storage/sessions.json',
            $_ENV['ADMIN_USER'] ?? '',
            $_ENV['ADMIN_PASSWORD_HASH'] ?? '',
            (int) ($_ENV['APP_TOKEN_TTL_MINUTES'] ?? 120)
        );
    });

    $container->set(HtmlView::class, function () use ($basePath): HtmlView {
        return new HtmlView(__DIR__ . '/../Views/web', $basePath);
    });

    $container->set(ResponseFactoryInterface::class, function (): ResponseFactoryInterface {
        return new ResponseFactory();
    });

    return $container;
}
