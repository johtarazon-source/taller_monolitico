<?php

namespace app\models\repositories\vehiculos_repository;

use app\models\entities\Vehiculo;

interface VehiculosRepositoryInterface
{
    public function get_vehiculos(array $filtros): array;

    public function find_vehiculo_by_id(int $id): ?Vehiculo;

    public function find_vehiculo_for_update(int $id): ?Vehiculo;

    public function exists_vehiculo(int $id): bool;

    public function save_new_vehiculo(Vehiculo $vehiculo): int;

    public function update_vehiculo(Vehiculo $vehiculo, bool $actualizar_estado): void;

    public function update_estado(int $id, string $estado): void;

    public function delete_vehiculo(int $id): void;

    public function has_reservas(int $id): bool;

    public function get_vehiculos_disponibles(string $fecha_inicio, string $fecha_fin): array;

    public function sync_estados(): void;
}
