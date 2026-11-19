<?php

namespace app\models\mappers;

use app\models\entities\Reserva;

class ReservaMappers
{
    public const COLUMNAS = 'r.id, r.cliente_id, r.vehiculo_id, r.fecha_inicio, r.fecha_fin, r.estado, r.created_at, r.updated_at';

    public const UNIONES = 'FROM reservas r '
        . 'INNER JOIN clientes c ON c.id = r.cliente_id '
        . 'INNER JOIN vehiculos v ON v.id = r.vehiculo_id';

    public static function columnas_con_relaciones(): string
    {
        return self::COLUMNAS . ', ' . ClienteMappers::COLUMNAS_CON_ALIAS . ', ' . VehiculoMappers::COLUMNAS_CON_ALIAS;
    }

    public static function to_entity(array $fila, bool $con_cliente = false, bool $con_vehiculo = false): Reserva
    {
        return new Reserva(
            (int) $fila['id'],
            (int) $fila['cliente_id'],
            (int) $fila['vehiculo_id'],
            $fila['fecha_inicio'],
            $fila['fecha_fin'],
            $fila['estado'],
            $fila['created_at'],
            $fila['updated_at'],
            $con_cliente ? ClienteMappers::to_entity($fila, 'c_') : null,
            $con_vehiculo ? VehiculoMappers::to_entity($fila, 'v_') : null
        );
    }
}
