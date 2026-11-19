<?php

namespace app\models\repositories\reportes_repository;

use app\models\database\AlquilerDbInterface;
use app\models\entities\Reserva;
use app\models\mappers\ReservaMappers;

class ReportesRepository implements ReportesRepositoryInterface
{
    private AlquilerDbInterface $alquiler_db;

    public function __construct(AlquilerDbInterface $alquiler_db)
    {
        $this->alquiler_db = $alquiler_db;
    }

    public function get_vehiculos_por_estado(): array
    {
        $conteos = [];

        foreach ($this->alquiler_db->select('SELECT estado, COUNT(*) AS total FROM vehiculos GROUP BY estado') as $fila) {
            $conteos[(string) $fila['estado']] = (int) $fila['total'];
        }

        return $conteos;
    }

    public function count_clientes_activos(): int
    {
        return $this->contar("SELECT COUNT(DISTINCT cliente_id) AS total FROM reservas WHERE estado = 'activa'");
    }

    public function count_clientes(): int
    {
        return $this->contar('SELECT COUNT(*) AS total FROM clientes');
    }

    public function count_reservas(): int
    {
        return $this->contar('SELECT COUNT(*) AS total FROM reservas');
    }

    public function count_reservas_activas(): int
    {
        return $this->contar("SELECT COUNT(*) AS total FROM reservas WHERE estado = 'activa'");
    }

    public function get_marcas(int $limite): array
    {
        $filas = $this->alquiler_db->select("SELECT DISTINCT marca FROM vehiculos ORDER BY marca ASC LIMIT {$limite}");

        return array_map(fn (array $fila): string => $fila['marca'], $filas);
    }

    public function get_reservas_recientes(int $limite): array
    {
        return $this->get_reservas('', 'r.id DESC', "LIMIT {$limite}");
    }

    public function get_reservas_vencidas(string $hoy): array
    {
        return $this->get_reservas("AND r.estado = 'activa' AND r.fecha_fin < ?", 'r.fecha_fin ASC', '', 's', [$hoy]);
    }

    private function contar(string $sql): int
    {
        return (int) $this->alquiler_db->select($sql)[0]['total'];
    }

    private function get_reservas(string $condiciones, string $orden, string $limite, string $tipos = '', array $parametros = []): array
    {
        $sql = 'SELECT ' . ReservaMappers::columnas_con_relaciones() . ' ' . ReservaMappers::UNIONES
            . " WHERE 1 = 1 {$condiciones} ORDER BY {$orden} {$limite}";

        return array_map(
            fn (array $fila): Reserva => ReservaMappers::to_entity($fila, true, true),
            $this->alquiler_db->select($sql, $tipos, $parametros)
        );
    }
}
