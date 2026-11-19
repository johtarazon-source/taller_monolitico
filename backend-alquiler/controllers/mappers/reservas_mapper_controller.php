<?php

namespace app\controllers\mappers;

use app\models\entities\Reserva;

class ReservasMapperController
{
    /**
     * @return array<string, mixed> la reserva tal como la devuelve la API, con su cliente y su vehiculo si se cargaron
     */
    public static function to_array(Reserva $reserva): array
    {
        $datos = [
            'id' => $reserva->get_id(),
            'cliente_id' => $reserva->get_cliente_id(),
            'vehiculo_id' => $reserva->get_vehiculo_id(),
            'fecha_inicio' => $reserva->get_fecha_inicio(),
            'fecha_fin' => $reserva->get_fecha_fin(),
            'estado' => $reserva->get_estado(),
            'created_at' => FormatoMapperController::marca_tiempo($reserva->get_created_at()),
            'updated_at' => FormatoMapperController::marca_tiempo($reserva->get_updated_at()),
            'estado_visible' => $reserva->get_estado_visible(),
        ];

        if ($reserva->get_cliente() !== null) {
            $datos['cliente'] = ClientesMapperController::to_array($reserva->get_cliente());
        }

        if ($reserva->get_vehiculo() !== null) {
            $datos['vehiculo'] = VehiculosMapperController::to_array($reserva->get_vehiculo());
        }

        return $datos;
    }

    /**
     * @param list<Reserva> $reservas
     * @return list<array<string, mixed>>
     */
    public static function to_array_list(array $reservas): array
    {
        return array_map(fn (Reserva $reserva): array => self::to_array($reserva), $reservas);
    }
}
