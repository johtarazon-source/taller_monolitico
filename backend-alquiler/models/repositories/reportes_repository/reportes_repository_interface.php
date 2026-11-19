<?php

namespace app\models\repositories\reportes_repository;

use app\models\entities\Reserva;

interface ReportesRepositoryInterface
{
    public function get_vehiculos_por_estado(): array;

    public function count_clientes_activos(): int;

    public function count_clientes(): int;

    public function count_reservas(): int;

    public function count_reservas_activas(): int;

    public function get_marcas(int $limite): array;

    public function get_reservas_recientes(int $limite): array;

    public function get_reservas_vencidas(string $hoy): array;
}
