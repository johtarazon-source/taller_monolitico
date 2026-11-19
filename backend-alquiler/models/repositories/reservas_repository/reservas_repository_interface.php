<?php

namespace app\models\repositories\reservas_repository;

use app\models\entities\Reserva;

interface ReservasRepositoryInterface
{
    /**
     * Reservas con su cliente y su vehiculo, de la mas reciente a la mas antigua.
     *
     * @param array<string, mixed> $filtros estado, cliente_id y vehiculo_id
     * @return list<Reserva>
     */
    public function get_reservas(array $filtros): array;

    /** Reserva con su cliente y su vehiculo. */
    public function find_reserva_by_id(int $id): ?Reserva;

    /**
     * Historial de un vehiculo (cada reserva trae su cliente).
     *
     * @return list<Reserva>
     */
    public function get_reservas_by_vehiculo(int $vehiculo_id): array;

    /**
     * Historial de un cliente (cada reserva trae su vehiculo).
     *
     * @return list<Reserva>
     */
    public function get_reservas_by_cliente(int $cliente_id): array;

    /** Inserta la reserva y devuelve su id. */
    public function save_new_reserva(Reserva $reserva): int;

    public function update_estado(int $id, string $estado): void;

    /** Indica si hay una reserva activa del vehiculo que choque con el rango (o que ya este vencida). */
    public function has_conflicto(int $vehiculo_id, string $fecha_inicio, string $fecha_fin): bool;

    public function begin_transaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
