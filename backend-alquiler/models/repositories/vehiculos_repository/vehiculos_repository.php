<?php

namespace app\models\repositories\vehiculos_repository;

use app\models\database\AlquilerDbInterface;
use app\models\entities\Vehiculo;
use app\models\mappers\VehiculoMappers;

class VehiculosRepository implements VehiculosRepositoryInterface
{
    private AlquilerDbInterface $alquiler_db;

    public function __construct(AlquilerDbInterface $alquiler_db)
    {
        $this->alquiler_db = $alquiler_db;
    }

    public function get_vehiculos(array $filtros): array
    {
        $sql = 'SELECT * FROM vehiculos WHERE 1 = 1';
        $tipos = '';
        $parametros = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND estado = ?';
            $tipos .= 's';
            $parametros[] = (string) $filtros['estado'];
        }

        if (!empty($filtros['categoria'])) {
            $sql .= ' AND categoria = ?';
            $tipos .= 's';
            $parametros[] = (string) $filtros['categoria'];
        }

        if (!empty($filtros['buscar'])) {
            $patron = '%' . trim((string) $filtros['buscar']) . '%';
            $sql .= ' AND (marca LIKE ? OR modelo LIKE ? OR categoria LIKE ?)';
            $tipos .= 'sss';
            array_push($parametros, $patron, $patron, $patron);
        }

        $sql .= ' ORDER BY marca ASC, modelo ASC';

        return array_map(
            fn (array $fila): Vehiculo => VehiculoMappers::to_entity($fila),
            $this->alquiler_db->select($sql, $tipos, $parametros)
        );
    }

    public function find_vehiculo_by_id(int $id): ?Vehiculo
    {
        $filas = $this->alquiler_db->select('SELECT * FROM vehiculos WHERE id = ? LIMIT 1', 'i', [$id]);

        return $filas === [] ? null : VehiculoMappers::to_entity($filas[0]);
    }

    public function find_vehiculo_for_update(int $id): ?Vehiculo
    {
        $filas = $this->alquiler_db->select('SELECT * FROM vehiculos WHERE id = ? LIMIT 1 FOR UPDATE', 'i', [$id]);

        return $filas === [] ? null : VehiculoMappers::to_entity($filas[0]);
    }

    public function exists_vehiculo(int $id): bool
    {
        return $this->alquiler_db->select('SELECT 1 FROM vehiculos WHERE id = ? LIMIT 1', 'i', [$id]) !== [];
    }

    public function save_new_vehiculo(Vehiculo $vehiculo): int
    {
        $ahora = date('Y-m-d H:i:s');

        $this->alquiler_db->execute(
            'INSERT INTO vehiculos (marca, modelo, anio, categoria, estado, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'ssissss',
            [
                $vehiculo->get_marca(),
                $vehiculo->get_modelo(),
                $vehiculo->get_anio(),
                $vehiculo->get_categoria(),
                $vehiculo->get_estado(),
                $ahora,
                $ahora,
            ]
        );

        return $this->alquiler_db->last_insert_id();
    }

    public function update_vehiculo(Vehiculo $vehiculo, bool $actualizar_estado): void
    {
        $sql = 'UPDATE vehiculos SET marca = ?, modelo = ?, anio = ?, categoria = ?';
        $tipos = 'ssis';
        $parametros = [$vehiculo->get_marca(), $vehiculo->get_modelo(), $vehiculo->get_anio(), $vehiculo->get_categoria()];

        if ($actualizar_estado) {
            $sql .= ', estado = ?';
            $tipos .= 's';
            $parametros[] = $vehiculo->get_estado();
        }

        $sql .= ', updated_at = ? WHERE id = ?';
        $tipos .= 'si';
        array_push($parametros, date('Y-m-d H:i:s'), $vehiculo->get_id());

        $this->alquiler_db->execute($sql, $tipos, $parametros);
    }

    public function update_estado(int $id, string $estado): void
    {
        $this->alquiler_db->execute('UPDATE vehiculos SET estado = ?, updated_at = ? WHERE id = ?', 'ssi', [$estado, date('Y-m-d H:i:s'), $id]);
    }

    public function delete_vehiculo(int $id): void
    {
        $this->alquiler_db->execute('DELETE FROM vehiculos WHERE id = ?', 'i', [$id]);
    }

    public function has_reservas(int $id): bool
    {
        return $this->alquiler_db->select('SELECT 1 FROM reservas WHERE vehiculo_id = ? LIMIT 1', 'i', [$id]) !== [];
    }

    public function get_vehiculos_disponibles(string $fecha_inicio, string $fecha_fin): array
    {
        $sql = "SELECT v.* FROM vehiculos v WHERE v.estado <> 'mantenimiento' "
            . "AND NOT EXISTS (SELECT 1 FROM reservas r WHERE r.vehiculo_id = v.id AND r.estado = 'activa' "
            . 'AND r.fecha_inicio <= ? AND (r.fecha_fin >= ? OR r.fecha_fin < ?)) '
            . 'ORDER BY v.marca, v.modelo';

        return array_map(
            fn (array $fila): Vehiculo => VehiculoMappers::to_entity($fila),
            $this->alquiler_db->select($sql, 'sss', [$fecha_fin, $fecha_inicio, date('Y-m-d')])
        );
    }

    public function sync_estados(): void
    {
        $this->alquiler_db->execute(
            "UPDATE vehiculos SET estado = CASE WHEN EXISTS (SELECT 1 FROM reservas r WHERE r.vehiculo_id = vehiculos.id "
            . "AND r.estado = 'activa' AND r.fecha_inicio <= ?) THEN 'alquilado' ELSE 'disponible' END "
            . "WHERE estado <> 'mantenimiento'",
            's',
            [date('Y-m-d')]
        );
    }
}
