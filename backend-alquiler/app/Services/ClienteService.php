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
    public function listar(?string $buscar): Collection
    {
        $query = Cliente::query()->orderBy('nombre');

        if ($buscar !== null && trim($buscar) !== '') {
            $texto = '%' . trim($buscar) . '%';
            $query->where(function (Builder $q) use ($texto): void {
                $q->where('nombre', 'like', $texto)
                    ->orWhere('correo', 'like', $texto)
                    ->orWhere('telefono', 'like', $texto)
                    ->orWhere('numero_licencia', 'like', $texto);
            });
        }

        return $query->get();
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
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Cliente
    {
        return Cliente::create($this->validar($datos))->refresh();
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): Cliente
    {
        $cliente = $this->obtener($id);
        $cliente->fill($this->validar($datos, $id))->save();

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
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    private function validar(array $datos, ?int $idActual = null): array
    {
        $v = new Validador($datos);

        $valores = [
            'nombre' => $v->texto('nombre', 'El nombre', 150, true),
            'telefono' => $v->texto('telefono', 'El teléfono', 50),
            'correo' => $v->correo('correo', 'El correo', 100),
            'numero_licencia' => $v->texto('numero_licencia', 'El número de licencia', 50),
        ];

        if ($valores['numero_licencia'] !== null && !$v->tieneError('numero_licencia')) {
            $duplicado = Cliente::query()
                ->where('numero_licencia', $valores['numero_licencia'])
                ->when($idActual !== null, fn (Builder $q) => $q->where('id', '<>', $idActual))
                ->exists();

            if ($duplicado) {
                $v->agregarError('numero_licencia', 'Ya existe un cliente con ese número de licencia.');
            }
        }

        $v->verificar();

        return $valores;
    }
}
