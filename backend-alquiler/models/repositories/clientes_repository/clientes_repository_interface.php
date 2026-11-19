<?php

namespace app\models\repositories\clientes_repository;

use app\models\entities\Cliente;

interface ClientesRepositoryInterface
{
    /**
     * @param string|null $buscar texto a buscar en nombre, correo, telefono o licencia
     * @return list<Cliente>
     */
    public function get_clientes(?string $buscar): array;

    public function find_cliente_by_id(int $id): ?Cliente;

    /** Inserta el cliente y devuelve su id. */
    public function save_new_cliente(Cliente $cliente): int;

    public function update_cliente(Cliente $cliente): void;

    public function delete_cliente(int $id): void;

    public function has_reservas(int $id): bool;

    /** Indica si ya existe un cliente con ese numero de licencia (sin contar a $id_excluido). */
    public function exists_licencia(string $numero_licencia, ?int $id_excluido = null): bool;
}
