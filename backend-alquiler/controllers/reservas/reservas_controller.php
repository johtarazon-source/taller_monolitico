<?php

namespace app\controllers\reservas;

use app\controllers\exceptions\ConflictException;
use app\controllers\exceptions\NotFoundException;
use app\controllers\mappers\ReservasMapperController;
use app\controllers\validators\Validator;
use app\models\entities\Reserva;
use app\models\entities\Vehiculo;
use app\models\repositories\clientes_repository\ClientesRepositoryInterface;
use app\models\repositories\reservas_repository\ReservasRepositoryInterface;
use app\models\repositories\vehiculos_repository\VehiculosRepositoryInterface;
use RuntimeException;
use Throwable;

class ReservasController implements ReservasControllerInterface
{
    private ReservasRepositoryInterface $reservas_repository;
    private VehiculosRepositoryInterface $vehiculos_repository;
    private ClientesRepositoryInterface $clientes_repository;

    public function __construct(
        ReservasRepositoryInterface $reservas_repository,
        VehiculosRepositoryInterface $vehiculos_repository,
        ClientesRepositoryInterface $clientes_repository
    ) {
        $this->reservas_repository = $reservas_repository;
        $this->vehiculos_repository = $vehiculos_repository;
        $this->clientes_repository = $clientes_repository;
    }

    public function get_reservas_all(array $filtros): array
    {
        $this->vehiculos_repository->sync_estados();

        return ReservasMapperController::to_array_list($this->reservas_repository->get_reservas($filtros));
    }

    public function get_reserva(int $id): array
    {
        return ReservasMapperController::to_array($this->obtener($id));
    }

    public function save_reserva(array $datos): array
    {
        $validador = new Validator($datos);
        $cliente_id = $validador->entero('cliente_id', 'El cliente', 1, PHP_INT_MAX, true);
        $vehiculo_id = $validador->entero('vehiculo_id', 'El vehículo', 1, PHP_INT_MAX, true);
        $fecha_inicio = $validador->fecha('fecha_inicio', 'La fecha de inicio', true);
        $fecha_fin = $validador->fecha('fecha_fin', 'La fecha de fin', true);

        if ($fecha_inicio !== null && $fecha_inicio < Reserva::hoy()) {
            $validador->agregar_error('fecha_inicio', 'La fecha de inicio no puede ser anterior a hoy.');
        }

        if ($fecha_inicio !== null && $fecha_fin !== null && $fecha_fin < $fecha_inicio) {
            $validador->agregar_error('fecha_fin', 'La fecha de fin no puede ser anterior a la de inicio.');
        }

        $cliente = $cliente_id !== null ? $this->clientes_repository->find_cliente_by_id($cliente_id) : null;

        if ($cliente_id !== null && $cliente === null) {
            $validador->agregar_error('cliente_id', 'El cliente no existe.');
        } elseif ($cliente !== null && trim((string) $cliente->get_numero_licencia()) === '') {
            $validador->agregar_error('cliente_id', 'El cliente no tiene número de licencia registrado; no puede alquilar.');
        }

        if ($vehiculo_id !== null && !$this->vehiculos_repository->exists_vehiculo($vehiculo_id)) {
            $validador->agregar_error('vehiculo_id', 'El vehículo no existe.');
        }

        $validador->verificar();

        $this->vehiculos_repository->sync_estados();

        $id = $this->guardar_si_esta_libre($cliente_id, $vehiculo_id, $fecha_inicio, $fecha_fin);

        $this->vehiculos_repository->sync_estados();

        return ReservasMapperController::to_array($this->obtener($id));
    }

    public function return_reserva(int $id, bool $mantenimiento): array
    {
        $reserva = $this->obtener_activa($id);

        if ($reserva->get_fecha_inicio() > Reserva::hoy()) {
            throw new ConflictException('La reserva aún no ha comenzado; si no se va a usar, cancélela.');
        }

        $this->reservas_repository->begin_transaction();

        try {
            $this->reservas_repository->update_estado($id, Reserva::COMPLETADA);

            if ($mantenimiento) {
                $this->vehiculos_repository->update_estado($reserva->get_vehiculo_id(), Vehiculo::MANTENIMIENTO);
            }

            $this->reservas_repository->commit();
        } catch (Throwable $error) {
            $this->reservas_repository->rollback();

            throw $error;
        }

        $this->vehiculos_repository->sync_estados();

        return ReservasMapperController::to_array($this->obtener($id));
    }

    public function cancel_reserva(int $id): array
    {
        $this->obtener_activa($id);
        $this->reservas_repository->update_estado($id, Reserva::CANCELADA);
        $this->vehiculos_repository->sync_estados();

        return ReservasMapperController::to_array($this->obtener($id));
    }

    /**
     * Crea la reserva dentro de una transaccion que bloquea la fila del vehiculo, para que dos
     * reservas simultaneas no se crucen.
     */
    private function guardar_si_esta_libre(int $cliente_id, int $vehiculo_id, string $fecha_inicio, string $fecha_fin): int
    {
        $this->reservas_repository->begin_transaction();

        try {
            $vehiculo = $this->vehiculos_repository->find_vehiculo_for_update($vehiculo_id);

            if ($vehiculo === null) {
                throw new RuntimeException('El vehículo no existe.');
            }

            if ($vehiculo->get_estado() === Vehiculo::MANTENIMIENTO) {
                throw new ConflictException('El vehículo está en mantenimiento y no se puede reservar.');
            }

            if ($this->reservas_repository->has_conflicto($vehiculo_id, $fecha_inicio, $fecha_fin)) {
                throw new ConflictException('El vehículo ya tiene una reserva activa que se cruza con esas fechas.');
            }

            $id = $this->reservas_repository->save_new_reserva(
                new Reserva(0, $cliente_id, $vehiculo_id, $fecha_inicio, $fecha_fin, Reserva::ACTIVA)
            );
            $this->reservas_repository->commit();

            return $id;
        } catch (Throwable $error) {
            $this->reservas_repository->rollback();

            throw $error;
        }
    }

    /**
     * @throws NotFoundException si no existe
     */
    private function obtener(int $id): Reserva
    {
        $reserva = $this->reservas_repository->find_reserva_by_id($id);

        if ($reserva === null) {
            throw new NotFoundException('La reserva no existe.');
        }

        return $reserva;
    }

    /**
     * @throws ConflictException si la reserva ya no esta activa
     */
    private function obtener_activa(int $id): Reserva
    {
        $reserva = $this->obtener($id);

        if ($reserva->get_estado() !== Reserva::ACTIVA) {
            throw new ConflictException("La reserva ya está {$reserva->get_estado()}.");
        }

        return $reserva;
    }
}
