<?php

namespace app\controllers\config;

use app\controllers\clientes\ClientesController;
use app\controllers\clientes\ClientesControllerInterface;
use app\controllers\reportes\ReportesController;
use app\controllers\reportes\ReportesControllerInterface;
use app\controllers\reservas\ReservasController;
use app\controllers\reservas\ReservasControllerInterface;
use app\controllers\sessions\SessionsController;
use app\controllers\sessions\SessionsControllerInterface;
use app\controllers\vehiculos\VehiculosController;
use app\controllers\vehiculos\VehiculosControllerInterface;
use app\models\config\Env;
use app\models\config\ModelsConfig;

class ControllersConfig
{
    private const ADMIN_USUARIO_POR_DEFECTO = 'admin';
    private const ADMIN_CONTRASENA_POR_DEFECTO = 'admin1234';

    public static function get_sessions_controller(): SessionsControllerInterface
    {
        $usuario = Env::obtener('ADMIN_USER');
        $contrasena = Env::obtener('ADMIN_PASSWORD');

        if ($usuario === null && $contrasena === null) {
            $usuario = self::ADMIN_USUARIO_POR_DEFECTO;
            $contrasena = self::ADMIN_CONTRASENA_POR_DEFECTO;
        }

        return new SessionsController(
            ModelsConfig::get_tokens_repository(),
            $usuario ?? '',
            $contrasena ?? '',
            (int) Env::obtener('APP_TOKEN_TTL_MINUTES', '120')
        );
    }

    public static function get_vehiculos_controller(): VehiculosControllerInterface
    {
        return new VehiculosController(ModelsConfig::get_vehiculos_repository(), ModelsConfig::get_reservas_repository());
    }

    public static function get_clientes_controller(): ClientesControllerInterface
    {
        return new ClientesController(ModelsConfig::get_clientes_repository(), ModelsConfig::get_reservas_repository());
    }

    public static function get_reservas_controller(): ReservasControllerInterface
    {
        return new ReservasController(
            ModelsConfig::get_reservas_repository(),
            ModelsConfig::get_vehiculos_repository(),
            ModelsConfig::get_clientes_repository()
        );
    }

    public static function get_reportes_controller(): ReportesControllerInterface
    {
        return new ReportesController(ModelsConfig::get_reportes_repository(), ModelsConfig::get_vehiculos_repository());
    }
}
