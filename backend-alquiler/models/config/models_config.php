<?php

namespace app\models\config;

use app\models\database\AlquilerDb;
use app\models\database\AlquilerDbInterface;
use app\models\repositories\clientes_repository\ClientesRepository;
use app\models\repositories\clientes_repository\ClientesRepositoryInterface;
use app\models\repositories\reportes_repository\ReportesRepository;
use app\models\repositories\reportes_repository\ReportesRepositoryInterface;
use app\models\repositories\reservas_repository\ReservasRepository;
use app\models\repositories\reservas_repository\ReservasRepositoryInterface;
use app\models\repositories\tokens_repository\TokensRepository;
use app\models\repositories\tokens_repository\TokensRepositoryInterface;
use app\models\repositories\vehiculos_repository\VehiculosRepository;
use app\models\repositories\vehiculos_repository\VehiculosRepositoryInterface;

class ModelsConfig
{
    private static ?AlquilerDbInterface $alquiler_db = null;

    public static function get_alquiler_db(): AlquilerDbInterface
    {
        return self::$alquiler_db ??= new AlquilerDb();
    }

    public static function get_vehiculos_repository(): VehiculosRepositoryInterface
    {
        return new VehiculosRepository(self::get_alquiler_db());
    }

    public static function get_clientes_repository(): ClientesRepositoryInterface
    {
        return new ClientesRepository(self::get_alquiler_db());
    }

    public static function get_reservas_repository(): ReservasRepositoryInterface
    {
        return new ReservasRepository(self::get_alquiler_db());
    }

    public static function get_reportes_repository(): ReportesRepositoryInterface
    {
        return new ReportesRepository(self::get_alquiler_db());
    }

    public static function get_tokens_repository(): TokensRepositoryInterface
    {
        return new TokensRepository(Env::obtener('SESSIONS_FILE') ?: dirname(__DIR__, 2) . '/sessions.json');
    }
}
