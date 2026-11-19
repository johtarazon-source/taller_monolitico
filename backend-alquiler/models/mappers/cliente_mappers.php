<?php

namespace app\models\mappers;

use app\models\entities\Cliente;

class ClienteMappers
{
    /** Columnas de un cliente con alias, para consultas que lo unen a otras tablas. */
    public const COLUMNAS_CON_ALIAS = 'c.id AS c_id, c.nombre AS c_nombre, c.telefono AS c_telefono, c.correo AS c_correo, '
        . 'c.numero_licencia AS c_numero_licencia, c.created_at AS c_created_at, c.updated_at AS c_updated_at';

    /**
     * @param array<string, mixed> $fila fila de la base de datos
     * @param string $prefijo prefijo de las columnas ("c_" cuando viene de un JOIN)
     */
    public static function to_entity(array $fila, string $prefijo = ''): Cliente
    {
        return new Cliente(
            (int) $fila[$prefijo . 'id'],
            $fila[$prefijo . 'nombre'],
            $fila[$prefijo . 'telefono'],
            $fila[$prefijo . 'correo'],
            $fila[$prefijo . 'numero_licencia'],
            $fila[$prefijo . 'created_at'],
            $fila[$prefijo . 'updated_at']
        );
    }
}
