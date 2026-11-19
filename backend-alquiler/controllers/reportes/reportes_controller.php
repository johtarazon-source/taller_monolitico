<?php

namespace app\controllers\reportes;

use app\controllers\mappers\ReservasMapperController;
use app\models\entities\Reserva;
use app\models\entities\Vehiculo;
use app\models\repositories\reportes_repository\ReportesRepositoryInterface;
use app\models\repositories\vehiculos_repository\VehiculosRepositoryInterface;

class ReportesController implements ReportesControllerInterface
{
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
        $hasta = date('Y-m-d', strtotime('+7 days'));
        $por_estado = $this->reportes_repository->get_vehiculos_por_estado();

        $por_categoria = [];

        foreach ($this->reportes_repository->get_vehiculos_por_categoria_y_estado() as $fila) {
            $categoria = $fila['categoria'] ?: 'Sin categoría';
            $por_categoria[$categoria] ??= [Vehiculo::DISPONIBLE => 0, Vehiculo::ALQUILADO => 0, Vehiculo::MANTENIMIENTO => 0];
            $por_categoria[$categoria][(string) $fila['estado']] = $fila['total'];
        }

        return [
            'fecha' => $hoy,
            'clientes_activos' => $this->reportes_repository->count_clientes_activos(),
            'reservas_total' => $this->reportes_repository->count_reservas(),
            'recientes' => ReservasMapperController::to_array_list($this->reportes_repository->get_reservas_recientes(5)),
            'marcas' => $this->reportes_repository->get_marcas(8),
            'vehiculos' => [
                'total' => array_sum($por_estado),
                Vehiculo::DISPONIBLE => $por_estado[Vehiculo::DISPONIBLE] ?? 0,
                Vehiculo::ALQUILADO => $por_estado[Vehiculo::ALQUILADO] ?? 0,
                Vehiculo::MANTENIMIENTO => $por_estado[Vehiculo::MANTENIMIENTO] ?? 0,
            ],
            'clientes' => $this->reportes_repository->count_clientes(),
            'reservas_activas' => $this->reportes_repository->count_reservas_activas(),
            'por_categoria' => $por_categoria,
            // Activas cuya fecha de fin ya paso sin registrar la devolucion.
            'vencidas' => ReservasMapperController::to_array_list($this->reportes_repository->get_reservas_vencidas($hoy)),
            // Devoluciones esperadas hoy o en los proximos 7 dias.
            'proximas_devoluciones' => ReservasMapperController::to_array_list(
                $this->reportes_repository->get_proximas_devoluciones($hoy, $hasta)
            ),
            // Reservas que comienzan en los proximos 7 dias.
            'proximas_salidas' => ReservasMapperController::to_array_list(
                $this->reportes_repository->get_proximas_salidas($hoy, $hasta)
            ),
        ];
    }
}
