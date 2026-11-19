<?php

namespace app\controllers\vehiculos;

use app\controllers\exceptions\ConflictException;
use app\controllers\exceptions\NotFoundException;
use app\controllers\mappers\ReservasMapperController;
use app\controllers\mappers\VehiculosMapperController;
use app\controllers\validators\Validator;
use app\models\entities\Reserva;
use app\models\entities\Vehiculo;
use app\models\repositories\reservas_repository\ReservasRepositoryInterface;
use app\models\repositories\vehiculos_repository\VehiculosRepositoryInterface;

class VehiculosController implements VehiculosControllerInterface
{
    private VehiculosRepositoryInterface $vehiculos_repository;
    private ReservasRepositoryInterface $reservas_repository;

    public function __construct(
        VehiculosRepositoryInterface $vehiculos_repository,
        ReservasRepositoryInterface $reservas_repository
    ) {
        $this->vehiculos_repository = $vehiculos_repository;
        $this->reservas_repository = $reservas_repository;
    }

    public function get_vehiculos_all(array $filtros): array
    {
        $this->vehiculos_repository->sync_estados();

        return VehiculosMapperController::to_array_list($this->vehiculos_repository->get_vehiculos($filtros));
    }

    public function get_vehiculo(int $id): array
    {
        return VehiculosMapperController::to_array($this->buscar_o_lanzar_no_encontrado($id));
    }

    public function save_vehiculo(array $datos): array
    {
        $campos = $this->validar($datos);
        $campos['estado'] ??= Vehiculo::DISPONIBLE;

        $id = $this->vehiculos_repository->save_new_vehiculo(VehiculosMapperController::to_entity($campos));

        return VehiculosMapperController::to_array($this->vehiculos_repository->find_vehiculo_by_id($id));
    }

    public function update_vehiculo(int $id, array $datos): array
    {
        $actual = $this->buscar_o_lanzar_no_encontrado($id);
        $campos = $this->validar($datos);

        if (($campos['estado'] ?? null) === Vehiculo::MANTENIMIENTO && $actual->get_estado() === Vehiculo::ALQUILADO) {
            throw new ConflictException('El vehículo está alquilado. Registre la devolución antes de enviarlo a mantenimiento.');
        }

        $this->vehiculos_repository->update_vehiculo(VehiculosMapperController::to_entity($campos, $id), isset($campos['estado']));
        $this->vehiculos_repository->sync_estados();

        return VehiculosMapperController::to_array($this->vehiculos_repository->find_vehiculo_by_id($id));
    }

    public function delete_vehiculo(int $id): void
    {
        $this->buscar_o_lanzar_no_encontrado($id);

        if ($this->vehiculos_repository->has_reservas($id)) {
            throw new ConflictException('El vehículo tiene reservas registradas y no se puede eliminar sin perder su historial.');
        }

        $this->vehiculos_repository->delete_vehiculo($id);
    }

    public function get_vehiculos_disponibles(?string $fecha_inicio, ?string $fecha_fin): array
    {
        $this->vehiculos_repository->sync_estados();

        $validador = new Validator(['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin]);
        $inicio = $validador->fecha('fecha_inicio', 'La fecha de inicio') ?? Reserva::hoy();
        $fin = $validador->fecha('fecha_fin', 'La fecha de fin') ?? $inicio;

        if (!$validador->tiene_error('fecha_inicio') && !$validador->tiene_error('fecha_fin') && $fin < $inicio) {
            $validador->agregar_error('fecha_fin', 'La fecha de fin no puede ser anterior a la de inicio.');
        }

        $validador->verificar();

        return [
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'vehiculos' => VehiculosMapperController::to_array_list(
                $this->vehiculos_repository->get_vehiculos_disponibles($inicio, $fin)
            ),
        ];
    }

    public function get_historial(int $id): array
    {
        $this->buscar_o_lanzar_no_encontrado($id);

        return ReservasMapperController::to_array_list($this->reservas_repository->get_reservas_by_vehiculo($id));
    }

    private function buscar_o_lanzar_no_encontrado(int $id): Vehiculo
    {
        $this->vehiculos_repository->sync_estados();
        $vehiculo = $this->vehiculos_repository->find_vehiculo_by_id($id);

        if ($vehiculo === null) {
            throw new NotFoundException('El vehículo no existe.');
        }

        return $vehiculo;
    }

    private function validar(array $datos): array
    {
        $validador = new Validator($datos);
        $anio_maximo = (int) date('Y') + 1;

        $campos = [
            'marca' => $validador->texto('marca', 'La marca', 100, true),
            'modelo' => $validador->texto('modelo', 'El modelo', 100, true),
            'anio' => $validador->entero('anio', 'El año', 1901, $anio_maximo, true),
            'categoria' => $validador->texto('categoria', 'La categoría', 50),
        ];

        $estado = $validador->opcion('estado', 'El estado', [Vehiculo::DISPONIBLE, Vehiculo::MANTENIMIENTO]);
        $validador->verificar();

        if ($estado !== null) {
            $campos['estado'] = $estado;
        }

        return $campos;
    }
}
