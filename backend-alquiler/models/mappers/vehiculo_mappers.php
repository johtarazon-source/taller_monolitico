<?php

namespace app\models\mappers;

use app\models\entities\Vehiculo;

class VehiculoMappers
{
    /** Columnas de un vehiculo con alias, para consultas que lo unen a otras tablas. */
    public const COLUMNAS_CON_ALIAS = 'v.id AS v_id, v.marca AS v_marca, v.modelo AS v_modelo, v.anio AS v_anio, '
        . 'v.categoria AS v_categoria, v.estado AS v_estado, v.created_at AS v_created_at, v.updated_at AS v_updated_at';

    /**
     * @param array<string, mixed> $fila fila de la base de datos
     * @param string $prefijo prefijo de las columnas ("v_" cuando viene de un JOIN)
     */
    public static function to_entity(array $fila, string $prefijo = ''): Vehiculo
    {
        return new Vehiculo(
            (int) $fila[$prefijo . 'id'],
            $fila[$prefijo . 'marca'],
            $fila[$prefijo . 'modelo'],
            (int) $fila[$prefijo . 'anio'],
            $fila[$prefijo . 'categoria'],
            $fila[$prefijo . 'estado'],
            $fila[$prefijo . 'created_at'],
            $fila[$prefijo . 'updated_at']
        );
    }
}
