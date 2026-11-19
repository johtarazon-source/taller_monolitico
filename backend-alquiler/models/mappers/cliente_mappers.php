<?php

namespace app\models\mappers;

use app\models\entities\Cliente;

class ClienteMappers
{
    public const COLUMNAS_CON_ALIAS = 'c.id AS c_id, c.nombre AS c_nombre, c.telefono AS c_telefono, c.correo AS c_correo, '
        . 'c.numero_licencia AS c_numero_licencia, c.created_at AS c_created_at, c.updated_at AS c_updated_at';

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
