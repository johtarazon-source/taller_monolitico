<?php

namespace app\models\repositories\reservas_repository;

use app\models\entities\Reserva;

interface ReservasRepositoryInterface
{
    public function get_reservas(array $filtros): array;

    public function find_reserva_by_id(int $id): ?Reserva;

    public function get_reservas_by_vehiculo(int $vehiculo_id): array;

    public function get_reservas_by_cliente(int $cliente_id): array;

    public function save_new_reserva(Reserva $reserva): int;

    public function update_estado(int $id, string $estado): void;

    public function has_conflicto(int $vehiculo_id, string $fecha_inicio, string $fecha_fin): bool;

    public function begin_transaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
