<?php

namespace app\controllers\clientes;

interface ClientesControllerInterface
{
    public function get_clientes_all(?string $buscar): array;

    public function get_cliente(int $id): array;

    public function save_cliente(array $datos): array;

    public function update_cliente(int $id, array $datos): array;

    public function delete_cliente(int $id): void;

    public function get_historial(int $id): array;
}
