<?php

namespace app\controllers\reservas;

interface ReservasControllerInterface
{
    public function get_reservas_all(array $filtros): array;

    public function get_reserva(int $id): array;

    public function save_reserva(array $datos): array;

    public function return_reserva(int $id, bool $mantenimiento): array;

    public function cancel_reserva(int $id): array;
}
