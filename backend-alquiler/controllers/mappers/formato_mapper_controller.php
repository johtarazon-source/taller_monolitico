<?php

namespace app\controllers\mappers;

use DateTime;
use DateTimeZone;

class FormatoMapperController
{
    public static function marca_tiempo(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $fecha = new DateTime($valor, new DateTimeZone(date_default_timezone_get()));

        return $fecha->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z');
    }
}
