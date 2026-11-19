<?php

namespace app\controllers\vehiculos;

interface VehiculosControllerInterface
{
    public function get_vehiculos_all(array $filtros): array;

    public function get_vehiculo(int $id): array;

    public function save_vehiculo(array $datos): array;

    public function update_vehiculo(int $id, array $datos): array;

    public function delete_vehiculo(int $id): void;

    public function get_vehiculos_disponibles(?string $fecha_inicio, ?string $fecha_fin): array;

    public function get_historial(int $id): array;
}
