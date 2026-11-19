<?php

namespace app\models\repositories\reportes_repository;

use app\models\entities\Reserva;

interface ReportesRepositoryInterface
{
    /** @return array<string, int> cantidad de vehiculos por estado */
    public function get_vehiculos_por_estado(): array;

    /** @return list<array{categoria: ?string, estado: ?string, total: int}> */
    public function get_vehiculos_por_categoria_y_estado(): array;

    /** Clientes distintos que tienen una reserva activa. */
    public function count_clientes_activos(): int;

    public function count_clientes(): int;

    public function count_reservas(): int;

    public function count_reservas_activas(): int;

    /** @return list<string> marcas distintas en orden alfabetico */
    public function get_marcas(int $limite): array;

    /** @return list<Reserva> las ultimas reservas creadas, con cliente y vehiculo */
    public function get_reservas_recientes(int $limite): array;

    /** @return list<Reserva> activas cuya fecha de fin ya paso */
    public function get_reservas_vencidas(string $hoy): array;

    /** @return list<Reserva> activas ya iniciadas que terminan entre hoy y $hasta */
    public function get_proximas_devoluciones(string $hoy, string $hasta): array;

    /** @return list<Reserva> activas que comienzan despues de hoy y hasta $hasta */
    public function get_proximas_salidas(string $hoy, string $hasta): array;
}
