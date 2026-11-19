<?php

namespace app\controllers\mappers;

use DateTime;
use DateTimeZone;

class FormatoMapperController
{
    /**
     * Convierte una marca de tiempo de la base de datos ("2026-11-19 10:00:00", en la zona horaria
     * de la aplicacion) al formato ISO 8601 en UTC que devuelve la API: "2026-11-19T15:00:00.000000Z".
     */
    public static function marca_tiempo(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $fecha = new DateTime($valor, new DateTimeZone(date_default_timezone_get()));

        return $fecha->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
