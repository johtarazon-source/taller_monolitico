<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Support\Validador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Registro y gestion de los clientes del negocio.
 */
final class ClienteService
{
    /**
     * @return Collection<int, Cliente>
     */
    public function listar(?string $terminoBusqueda): Collection
    {
        $consulta = Cliente::query()->orderBy('nombre');

        if ($terminoBusqueda !== null && trim($terminoBusqueda) !== '') {
            $patronBusqueda = '%' . trim($terminoBusqueda) . '%';
            $consulta->where(function (Builder $consultaBusqueda) use ($patronBusqueda): void {
                $consultaBusqueda->where('nombre', 'like', $patronBusqueda)
                    ->orWhere('correo', 'like', $patronBusqueda)
                    ->orWhere('telefono', 'like', $patronBusqueda)
                    ->orWhere('numero_licencia', 'like', $patronBusqueda);
            });
        }

        return $consulta->get();
    }

    public function obtener(int $id): Cliente
    {
        $cliente = Cliente::find($id);

        if ($cliente === null) {
            throw new NotFoundException('El cliente no existe.');
        }

        return $cliente;
    }

    /**
     * @param array<string, mixed> $datosCliente
     */
    public function crear(array $datosCliente): Cliente
    {
        return Cliente::create($this->validar($datosCliente))->refresh();
    }

    /**
     * @param array<string, mixed> $datosCliente
     */
    public function actualizar(int $id, array $datosCliente): Cliente
    {
        $cliente = $this->obtener($id);
        $cliente->fill($this->validar($datosCliente, $id))->save();

        return $cliente->refresh();
    }

    public function eliminar(int $id): void
    {
        $cliente = $this->obtener($id);

        // La BD borra en cascada las reservas: se impide para no perder el historial.
        if ($cliente->reservas()->exists()) {
            throw new ConflictException('El cliente tiene reservas registradas y no se puede eliminar sin perder su historial.');
        }

        $cliente->delete();
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function historial(int $id): Collection
    {
        return $this->obtener($id)
            ->reservas()
            ->with('vehiculo')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param array<string, mixed> $datosCliente
     * @return array<string, mixed>
     */
    private function validar(array $datosCliente, ?int $idActual = null): array
    {
        $validador = new Validador($datosCliente);

        $camposValidados = [
            'nombre' => $validador->texto('nombre', 'El nombre', 150, true),
            'telefono' => $validador->texto('telefono', 'El teléfono', 50),
            'correo' => $validador->correo('correo', 'El correo', 100),
            'numero_licencia' => $validador->texto('numero_licencia', 'El número de licencia', 50),
        ];

        if ($camposValidados['numero_licencia'] !== null && !$validador->tieneError('numero_licencia')) {
            $duplicado = Cliente::query()
                ->where('numero_licencia', $camposValidados['numero_licencia'])
                ->when($idActual !== null, fn (Builder $consultaCliente) => $consultaCliente->where('id', '<>', $idActual))
                ->exists();

            if ($duplicado) {
                $validador->agregarError('numero_licencia', 'Ya existe un cliente con ese número de licencia.');
            }
        }

        $validador->verificar();

        return $camposValidados;
    }
}
