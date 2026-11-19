<?php

namespace app\models\repositories\reservas_repository;

use app\models\database\AlquilerDbInterface;
use app\models\entities\Reserva;
use app\models\mappers\ClienteMappers;
use app\models\mappers\ReservaMappers;
use app\models\mappers\VehiculoMappers;

class ReservasRepository implements ReservasRepositoryInterface
{
    private AlquilerDbInterface $alquiler_db;

    public function __construct(AlquilerDbInterface $alquiler_db)
    {
        $this->alquiler_db = $alquiler_db;
    }

    public function get_reservas(array $filtros): array
    {
        $sql = 'SELECT ' . ReservaMappers::columnas_con_relaciones() . ' ' . ReservaMappers::UNIONES . ' WHERE 1 = 1';
        $tipos = '';
        $parametros = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND r.estado = ?';
            $tipos .= 's';
            $parametros[] = (string) $filtros['estado'];
        }

        if (!empty($filtros['cliente_id'])) {
            $sql .= ' AND r.cliente_id = ?';
            $tipos .= 'i';
            $parametros[] = (int) $filtros['cliente_id'];
        }

        if (!empty($filtros['vehiculo_id'])) {
            $sql .= ' AND r.vehiculo_id = ?';
            $tipos .= 'i';
            $parametros[] = (int) $filtros['vehiculo_id'];
        }

        $sql .= ' ORDER BY r.fecha_inicio DESC, r.id DESC';

        return array_map(
            fn (array $fila): Reserva => ReservaMappers::to_entity($fila, true, true),
            $this->alquiler_db->select($sql, $tipos, $parametros)
        );
    }

    public function find_reserva_by_id(int $id): ?Reserva
    {
        $filas = $this->alquiler_db->select(
            'SELECT ' . ReservaMappers::columnas_con_relaciones() . ' ' . ReservaMappers::UNIONES . ' WHERE r.id = ? LIMIT 1',
            'i',
            [$id]
        );

        return $filas === [] ? null : ReservaMappers::to_entity($filas[0], true, true);
    }

    public function get_reservas_by_vehiculo(int $vehiculo_id): array
    {
        $sql = 'SELECT ' . ReservaMappers::COLUMNAS . ', ' . ClienteMappers::COLUMNAS_CON_ALIAS . ' '
            . 'FROM reservas r INNER JOIN clientes c ON c.id = r.cliente_id '
            . 'WHERE r.vehiculo_id = ? ORDER BY r.fecha_inicio DESC, r.id DESC';

        return array_map(
            fn (array $fila): Reserva => ReservaMappers::to_entity($fila, true, false),
            $this->alquiler_db->select($sql, 'i', [$vehiculo_id])
        );
    }

    public function get_reservas_by_cliente(int $cliente_id): array
    {
        $sql = 'SELECT ' . ReservaMappers::COLUMNAS . ', ' . VehiculoMappers::COLUMNAS_CON_ALIAS . ' '
            . 'FROM reservas r INNER JOIN vehiculos v ON v.id = r.vehiculo_id '
            . 'WHERE r.cliente_id = ? ORDER BY r.fecha_inicio DESC, r.id DESC';

        return array_map(
            fn (array $fila): Reserva => ReservaMappers::to_entity($fila, false, true),
            $this->alquiler_db->select($sql, 'i', [$cliente_id])
        );
    }

    public function save_new_reserva(Reserva $reserva): int
    {
        $ahora = date('Y-m-d H:i:s');

        $this->alquiler_db->execute(
            'INSERT INTO reservas (cliente_id, vehiculo_id, fecha_inicio, fecha_fin, estado, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'iisssss',
            [
                $reserva->get_cliente_id(),
                $reserva->get_vehiculo_id(),
                $reserva->get_fecha_inicio(),
                $reserva->get_fecha_fin(),
                $reserva->get_estado(),
                $ahora,
                $ahora,
            ]
        );

        return $this->alquiler_db->last_insert_id();
    }

    public function update_estado(int $id, string $estado): void
    {
        $this->alquiler_db->execute('UPDATE reservas SET estado = ?, updated_at = ? WHERE id = ?', 'ssi', [$estado, date('Y-m-d H:i:s'), $id]);
    }

    public function has_conflicto(int $vehiculo_id, string $fecha_inicio, string $fecha_fin): bool
    {
        $sql = "SELECT 1 FROM reservas WHERE vehiculo_id = ? AND estado = 'activa' AND fecha_inicio <= ? "
            . 'AND (fecha_fin >= ? OR fecha_fin < ?) LIMIT 1';

        return $this->alquiler_db->select($sql, 'isss', [$vehiculo_id, $fecha_fin, $fecha_inicio, date('Y-m-d')]) !== [];
    }

    public function begin_transaction(): void
    {
        $this->alquiler_db->begin_transaction();
    }

    public function commit(): void
    {
        $this->alquiler_db->commit();
    }

    public function rollback(): void
    {
        $this->alquiler_db->rollback();
    }
}
