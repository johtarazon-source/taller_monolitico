<?php

namespace app\controllers\mappers;

use app\models\entities\Vehiculo;

class VehiculosMapperController
{
    public static function to_entity(array $datos, int $id = 0): Vehiculo
    {
        return new Vehiculo(
            $id,
            $datos['marca'],
            $datos['modelo'],
            $datos['anio'],
            $datos['categoria'] ?? null,
            $datos['estado'] ?? null
        );
    }

    public static function to_array(Vehiculo $vehiculo): array
    {
        return [
            'id' => $vehiculo->get_id(),
            'marca' => $vehiculo->get_marca(),
            'modelo' => $vehiculo->get_modelo(),
            'anio' => $vehiculo->get_anio(),
            'categoria' => $vehiculo->get_categoria(),
            'estado' => $vehiculo->get_estado(),
            'created_at' => FormatoMapperController::marca_tiempo($vehiculo->get_created_at()),
            'updated_at' => FormatoMapperController::marca_tiempo($vehiculo->get_updated_at()),
        ];
    }

    public static function to_array_list(array $vehiculos): array
    {
        return array_map(fn (Vehiculo $vehiculo): array => self::to_array($vehiculo), $vehiculos);
    }
}
