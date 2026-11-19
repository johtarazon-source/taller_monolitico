<?php

namespace app\controllers\clientes;

interface ClientesControllerInterface
{
    /** @return list<array<string, mixed>> */
    public function get_clientes_all(?string $buscar): array;

    /** @return array<string, mixed> */
    public function get_cliente(int $id): array;

    /**
     * @param array<string, mixed> $datos nombre, telefono, correo y numero_licencia
     * @return array<string, mixed> el cliente creado
     */
    public function save_cliente(array $datos): array;

    /**
     * @param array<string, mixed> $datos
     * @return array<string, mixed> el cliente actualizado
     */
    public function update_cliente(int $id, array $datos): array;

    public function delete_cliente(int $id): void;

    /**
     * Reservas del cliente, cada una con su vehiculo.
     *
     * @return list<array<string, mixed>>
     */
    public function get_historial(int $id): array;
}
