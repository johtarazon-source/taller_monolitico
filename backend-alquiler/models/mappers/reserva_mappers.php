<?php

namespace app\models\mappers;

use app\models\entities\Reserva;

class ReservaMappers
{
    public const COLUMNAS = 'r.id, r.cliente_id, r.vehiculo_id, r.fecha_inicio, r.fecha_fin, r.estado, r.created_at, r.updated_at';

    /** Une la reserva con su cliente y su vehiculo (siempre existen por las claves foraneas). */
    public const UNIONES = 'FROM reservas r '
        . 'INNER JOIN clientes c ON c.id = r.cliente_id '
        . 'INNER JOIN vehiculos v ON v.id = r.vehiculo_id';

    /** Columnas para consultar reservas con sus dos relaciones. */
    public static function columnas_con_relaciones(): string
    {
        return self::COLUMNAS . ', ' . ClienteMappers::COLUMNAS_CON_ALIAS . ', ' . VehiculoMappers::COLUMNAS_CON_ALIAS;
    }

    /**
     * @param array<string, mixed> $fila fila de la base de datos
     * @param bool $con_cliente la fila trae las columnas del cliente con prefijo "c_"
     * @param bool $con_vehiculo la fila trae las columnas del vehiculo con prefijo "v_"
     */
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
