<?php

namespace app\controllers\mappers;

use app\models\entities\Vehiculo;

class VehiculosMapperController
{
    /**
     * Arma la entidad con datos ya validados (marca, modelo, anio, categoria y, opcional, estado).
     *
     * @param array<string, mixed> $datos
     */
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

    /**
     * @return array<string, mixed> el vehiculo tal como lo devuelve la API
     */
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
}
