<?php

namespace app\models\repositories\clientes_repository;

use app\models\database\AlquilerDbInterface;
use app\models\entities\Cliente;
use app\models\mappers\ClienteMappers;

class ClientesRepository implements ClientesRepositoryInterface
{
    private AlquilerDbInterface $alquiler_db;

    public function __construct(AlquilerDbInterface $alquiler_db)
    {
        $this->alquiler_db = $alquiler_db;
    }

    public function get_clientes(?string $buscar): array
    {
        $sql = 'SELECT * FROM clientes';
        $tipos = '';
        $parametros = [];

        if ($buscar !== null && trim($buscar) !== '') {
            $patron = '%' . trim($buscar) . '%';
            $sql .= ' WHERE (nombre LIKE ? OR correo LIKE ? OR telefono LIKE ? OR numero_licencia LIKE ?)';
            $tipos = 'ssss';
            $parametros = [$patron, $patron, $patron, $patron];
        }

        $sql .= ' ORDER BY nombre ASC';

        return array_map(
            fn (array $fila): Cliente => ClienteMappers::to_entity($fila),
            $this->alquiler_db->select($sql, $tipos, $parametros)
        );
    }

    public function find_cliente_by_id(int $id): ?Cliente
    {
        $filas = $this->alquiler_db->select('SELECT * FROM clientes WHERE id = ? LIMIT 1', 'i', [$id]);

        return $filas === [] ? null : ClienteMappers::to_entity($filas[0]);
    }

    public function save_new_cliente(Cliente $cliente): int
    {
        $ahora = date('Y-m-d H:i:s');

        $this->alquiler_db->execute(
            'INSERT INTO clientes (nombre, telefono, correo, numero_licencia, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            'ssssss',
            [
                $cliente->get_nombre(),
                $cliente->get_telefono(),
                $cliente->get_correo(),
                $cliente->get_numero_licencia(),
                $ahora,
                $ahora,
            ]
        );

        return $this->alquiler_db->last_insert_id();
    }

    public function update_cliente(Cliente $cliente): void
    {
        $this->alquiler_db->execute(
            'UPDATE clientes SET nombre = ?, telefono = ?, correo = ?, numero_licencia = ?, updated_at = ? WHERE id = ?',
            'sssssi',
            [
                $cliente->get_nombre(),
                $cliente->get_telefono(),
                $cliente->get_correo(),
                $cliente->get_numero_licencia(),
                date('Y-m-d H:i:s'),
                $cliente->get_id(),
            ]
        );
    }

    public function delete_cliente(int $id): void
    {
        $this->alquiler_db->execute('DELETE FROM clientes WHERE id = ?', 'i', [$id]);
    }

    public function has_reservas(int $id): bool
    {
        return $this->alquiler_db->select('SELECT 1 FROM reservas WHERE cliente_id = ? LIMIT 1', 'i', [$id]) !== [];
    }

    public function exists_licencia(string $numero_licencia, ?int $id_excluido = null): bool
    {
        if ($id_excluido === null) {
            return $this->alquiler_db->select('SELECT 1 FROM clientes WHERE numero_licencia = ? LIMIT 1', 's', [$numero_licencia]) !== [];
        }

        return $this->alquiler_db->select(
            'SELECT 1 FROM clientes WHERE numero_licencia = ? AND id <> ? LIMIT 1',
            'si',
            [$numero_licencia, $id_excluido]
        ) !== [];
    }
}
