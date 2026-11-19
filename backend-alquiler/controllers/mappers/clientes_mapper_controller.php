<?php

namespace app\controllers\mappers;

use app\models\entities\Cliente;

class ClientesMapperController
{
    /**
     * Arma la entidad con datos ya validados (nombre, telefono, correo y numero_licencia).
     *
     * @param array<string, mixed> $datos
     */
    public static function to_entity(array $datos, int $id = 0): Cliente
    {
        return new Cliente($id, $datos['nombre'], $datos['telefono'], $datos['correo'], $datos['numero_licencia']);
    }

    /**
     * @return array<string, mixed> el cliente tal como lo devuelve la API
     */
    public static function to_array(Cliente $cliente): array
    {
        return [
            'id' => $cliente->get_id(),
            'nombre' => $cliente->get_nombre(),
            'telefono' => $cliente->get_telefono(),
            'correo' => $cliente->get_correo(),
            'numero_licencia' => $cliente->get_numero_licencia(),
            'created_at' => FormatoMapperController::marca_tiempo($cliente->get_created_at()),
            'updated_at' => FormatoMapperController::marca_tiempo($cliente->get_updated_at()),
        ];
    }
}
