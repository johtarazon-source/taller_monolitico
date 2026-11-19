<?php

namespace app\controllers\reservas;

interface ReservasControllerInterface
{
    /**
     * @param array<string, mixed> $filtros estado, cliente_id y vehiculo_id
     * @return list<array<string, mixed>> reservas con su cliente y su vehiculo
     */
    public function get_reservas_all(array $filtros): array;

    /** @return array<string, mixed> */
    public function get_reserva(int $id): array;

    /**
     * @param array<string, mixed> $datos cliente_id, vehiculo_id, fecha_inicio y fecha_fin
     * @return array<string, mixed> la reserva creada
     */
    public function save_reserva(array $datos): array;

    /**
     * Registra la devolucion. Con $mantenimiento el vehiculo queda en mantenimiento.
     *
     * @return array<string, mixed>
     */
    public function return_reserva(int $id, bool $mantenimiento): array;

    /** @return array<string, mixed> */
    public function cancel_reserva(int $id): array;
}
