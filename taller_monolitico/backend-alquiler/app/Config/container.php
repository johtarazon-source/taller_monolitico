<?php

declare(strict_types=1);

use App\Services\AuthService;
use DI\Container;

/**
 * Construye el contenedor de inyeccion de dependencias de la aplicacion.
 * Los servicios de dominio (Vehiculo, Cliente, Reserva) se registran aqui
 * en las fases siguientes.
 */
function buildContainer(): Container
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

    return $container;
}
