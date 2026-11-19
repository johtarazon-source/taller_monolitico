<?php

namespace app\controllers\vehiculos;

interface VehiculosControllerInterface
{
    /**
     * @param array<string, mixed> $filtros estado, categoria y buscar
     * @return list<array<string, mixed>>
     */
    public function get_vehiculos_all(array $filtros): array;

    /** @return array<string, mixed> */
    public function get_vehiculo(int $id): array;

    /**
     * @param array<string, mixed> $datos marca, modelo, anio, categoria y estado
     * @return array<string, mixed> el vehiculo creado
     */
    public function save_vehiculo(array $datos): array;

    /**
     * @param array<string, mixed> $datos
     * @return array<string, mixed> el vehiculo actualizado
     */
    public function update_vehiculo(int $id, array $datos): array;

    public function delete_vehiculo(int $id): void;

    /**
     * @return array{fecha_inicio: string, fecha_fin: string, vehiculos: list<array<string, mixed>>}
     */
    public function get_vehiculos_disponibles(?string $fecha_inicio, ?string $fecha_fin): array;

    /**
     * Reservas del vehiculo, cada una con su cliente.
     *
     * @return list<array<string, mixed>>
     */
    public function get_historial(int $id): array;
}
