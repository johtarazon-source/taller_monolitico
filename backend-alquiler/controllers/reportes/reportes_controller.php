<?php

namespace app\controllers\reportes;

use app\controllers\mappers\ReservasMapperController;
use app\models\entities\Reserva;
use app\models\entities\Vehiculo;
use app\models\repositories\reportes_repository\ReportesRepositoryInterface;
use app\models\repositories\vehiculos_repository\VehiculosRepositoryInterface;

class ReportesController implements ReportesControllerInterface
{
    private const LIMITE_RESERVAS_RECIENTES = 5;
    private const LIMITE_MARCAS = 8;

    private ReportesRepositoryInterface $reportes_repository;
    private VehiculosRepositoryInterface $vehiculos_repository;

    public function __construct(
        ReportesRepositoryInterface $reportes_repository,
        VehiculosRepositoryInterface $vehiculos_repository
    ) {
        $this->reportes_repository = $reportes_repository;
        $this->vehiculos_repository = $vehiculos_repository;
    }

    public function get_resumen(): array
    {
        $this->vehiculos_repository->sync_estados();

        $hoy = Reserva::hoy();
        $por_estado = $this->reportes_repository->get_vehiculos_por_estado();

        return [
            'fecha' => $hoy,
            'clientes_activos' => $this->reportes_repository->count_clientes_activos(),
            'reservas_total' => $this->reportes_repository->count_reservas(),
            'recientes' => ReservasMapperController::to_array_list($this->reportes_repository->get_reservas_recientes(self::LIMITE_RESERVAS_RECIENTES)),
            'marcas' => $this->reportes_repository->get_marcas(self::LIMITE_MARCAS),
            'vehiculos' => [
                'total' => array_sum($por_estado),
                Vehiculo::DISPONIBLE => $por_estado[Vehiculo::DISPONIBLE] ?? 0,
                Vehiculo::ALQUILADO => $por_estado[Vehiculo::ALQUILADO] ?? 0,
                Vehiculo::MANTENIMIENTO => $por_estado[Vehiculo::MANTENIMIENTO] ?? 0,
            ],
            'clientes' => $this->reportes_repository->count_clientes(),
            'reservas_activas' => $this->reportes_repository->count_reservas_activas(),
            'vencidas' => ReservasMapperController::to_array_list($this->reportes_repository->get_reservas_vencidas($hoy)),
        ];
    }
}
