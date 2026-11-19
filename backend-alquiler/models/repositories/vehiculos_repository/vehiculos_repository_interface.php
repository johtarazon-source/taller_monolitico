<?php

namespace app\models\repositories\vehiculos_repository;

use app\models\entities\Vehiculo;

interface VehiculosRepositoryInterface
{
    /**
     * @param array<string, mixed> $filtros estado, categoria y buscar (marca, modelo o categoria)
     * @return list<Vehiculo>
     */
    public function get_vehiculos(array $filtros): array;

    public function find_vehiculo_by_id(int $id): ?Vehiculo;

    /** Igual que find_vehiculo_by_id, pero bloquea la fila hasta terminar la transaccion. */
    public function find_vehiculo_for_update(int $id): ?Vehiculo;

    public function exists_vehiculo(int $id): bool;

    /** Inserta el vehiculo y devuelve su id. */
    public function save_new_vehiculo(Vehiculo $vehiculo): int;

    /** Actualiza los datos del vehiculo; el estado solo se toca si $actualizar_estado es true. */
    public function update_vehiculo(Vehiculo $vehiculo, bool $actualizar_estado): void;

    public function update_estado(int $id, string $estado): void;

    public function delete_vehiculo(int $id): void;

    public function has_reservas(int $id): bool;

    /**
     * Vehiculos que no estan en mantenimiento y no tienen reservas activas que choquen con el rango.
     *
     * @return list<Vehiculo>
     */
    public function get_vehiculos_disponibles(string $fecha_inicio, string $fecha_fin): array;

    /**
     * Pone cada vehiculo (salvo los de mantenimiento) en "alquilado" si tiene una reserva activa
     * que ya comenzo, o en "disponible" si no.
     */
    public function sync_estados(): void;
}
