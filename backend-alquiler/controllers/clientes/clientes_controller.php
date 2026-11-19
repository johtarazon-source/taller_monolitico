<?php

namespace app\controllers\clientes;

use app\controllers\exceptions\ConflictException;
use app\controllers\exceptions\NotFoundException;
use app\controllers\mappers\ClientesMapperController;
use app\controllers\mappers\ReservasMapperController;
use app\controllers\validators\Validator;
use app\models\entities\Cliente;
use app\models\repositories\clientes_repository\ClientesRepositoryInterface;
use app\models\repositories\reservas_repository\ReservasRepositoryInterface;

class ClientesController implements ClientesControllerInterface
{
    private ClientesRepositoryInterface $clientes_repository;
    private ReservasRepositoryInterface $reservas_repository;

    public function __construct(
        ClientesRepositoryInterface $clientes_repository,
        ReservasRepositoryInterface $reservas_repository
    ) {
        $this->clientes_repository = $clientes_repository;
        $this->reservas_repository = $reservas_repository;
    }

    public function get_clientes_all(?string $buscar): array
    {
        return ClientesMapperController::to_array_list($this->clientes_repository->get_clientes($buscar));
    }

    public function get_cliente(int $id): array
    {
        return ClientesMapperController::to_array($this->buscar_o_lanzar_no_encontrado($id));
    }

    public function save_cliente(array $datos): array
    {
        $campos = $this->validar($datos);
        $id = $this->clientes_repository->save_new_cliente(ClientesMapperController::to_entity($campos));

        return ClientesMapperController::to_array($this->clientes_repository->find_cliente_by_id($id));
    }

    public function update_cliente(int $id, array $datos): array
    {
        $this->buscar_o_lanzar_no_encontrado($id);
        $campos = $this->validar($datos, $id);

        $this->clientes_repository->update_cliente(ClientesMapperController::to_entity($campos, $id));

        return ClientesMapperController::to_array($this->clientes_repository->find_cliente_by_id($id));
    }

    public function delete_cliente(int $id): void
    {
        $this->buscar_o_lanzar_no_encontrado($id);

        if ($this->clientes_repository->has_reservas($id)) {
            throw new ConflictException('El cliente tiene reservas registradas y no se puede eliminar sin perder su historial.');
        }

        $this->clientes_repository->delete_cliente($id);
    }

    public function get_historial(int $id): array
    {
        $this->buscar_o_lanzar_no_encontrado($id);

        return ReservasMapperController::to_array_list($this->reservas_repository->get_reservas_by_cliente($id));
    }

    private function buscar_o_lanzar_no_encontrado(int $id): Cliente
    {
        $cliente = $this->clientes_repository->find_cliente_by_id($id);

        if ($cliente === null) {
            throw new NotFoundException('El cliente no existe.');
        }

        return $cliente;
    }

    private function validar(array $datos, ?int $id_actual = null): array
    {
        $validador = new Validator($datos);

        $campos = [
            'nombre' => $validador->texto('nombre', 'El nombre', 150, true),
            'telefono' => $validador->texto('telefono', 'El teléfono', 50),
            'correo' => $validador->correo('correo', 'El correo', 100),
            'numero_licencia' => $validador->texto('numero_licencia', 'El número de licencia', 50),
        ];

        if ($campos['numero_licencia'] !== null && !$validador->tiene_error('numero_licencia')
            && $this->clientes_repository->exists_licencia($campos['numero_licencia'], $id_actual)) {
            $validador->agregar_error('numero_licencia', 'Ya existe un cliente con ese número de licencia.');
        }

        $validador->verificar();

        return $campos;
    }
}
