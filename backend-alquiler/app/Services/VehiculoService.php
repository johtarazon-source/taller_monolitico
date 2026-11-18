<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Support\Validador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Registro y gestion de la flota de vehiculos.
 */
final class VehiculoService
{
    public function __construct(private DisponibilidadService $servicioDisponibilidad)
    {
    }

    /**
     * @param array<string, mixed> $filtros estado, categoria, buscar
     * @return Collection<int, Vehiculo>
     */
    public function listar(array $filtros): Collection
    {
        $this->servicioDisponibilidad->sincronizarEstados();

        $consulta = Vehiculo::query()->orderBy('marca')->orderBy('modelo');

        if (!empty($filtros['estado'])) {
            $consulta->where('estado', (string) $filtros['estado']);
        }

        if (!empty($filtros['categoria'])) {
            $consulta->where('categoria', (string) $filtros['categoria']);
        }

        if (!empty($filtros['buscar'])) {
            $patronBusqueda = '%' . trim((string) $filtros['buscar']) . '%';
            $consulta->where(function (Builder $consultaBusqueda) use ($patronBusqueda): void {
                $consultaBusqueda->where('marca', 'like', $patronBusqueda)
                    ->orWhere('modelo', 'like', $patronBusqueda)
                    ->orWhere('categoria', 'like', $patronBusqueda);
            });
        }

        return $consulta->get();
    }

    public function obtener(int $id): Vehiculo
    {
        $this->servicioDisponibilidad->sincronizarEstados();

        $vehiculo = Vehiculo::find($id);

        if ($vehiculo === null) {
            throw new NotFoundException('El vehículo no existe.');
        }

        return $vehiculo;
    }

    /**
     * @param array<string, mixed> $datosVehiculo
     */
    public function crear(array $datosVehiculo): Vehiculo
    {
        $camposValidados = $this->validar($datosVehiculo);
        $camposValidados['estado'] ??= Vehiculo::DISPONIBLE;

        return Vehiculo::create($camposValidados)->refresh();
    }

    /**
     * @param array<string, mixed> $datosVehiculo
     */
    public function actualizar(int $id, array $datosVehiculo): Vehiculo
    {
        $vehiculo = $this->obtener($id);
        $camposValidados = $this->validar($datosVehiculo);

        if (($camposValidados['estado'] ?? null) === Vehiculo::MANTENIMIENTO && $vehiculo->estado === Vehiculo::ALQUILADO) {
            throw new ConflictException('El vehículo está alquilado. Registre la devolución antes de enviarlo a mantenimiento.');
        }

        // Si se envia "disponible" estando alquilado, la sincronizacion lo deja en alquilado.
        $vehiculo->fill($camposValidados)->save();
        $this->servicioDisponibilidad->sincronizarEstados();

        return $vehiculo->refresh();
    }

    public function eliminar(int $id): void
    {
        $vehiculo = $this->obtener($id);

        // La BD borra en cascada las reservas: se impide para no perder el historial.
        if ($vehiculo->reservas()->exists()) {
            throw new ConflictException('El vehículo tiene reservas registradas y no se puede eliminar sin perder su historial.');
        }

        $vehiculo->delete();
    }

    /**
     * Vehiculos que se pueden reservar en el rango dado (por defecto, hoy).
     *
     * @return array{fecha_inicio: string, fecha_fin: string, vehiculos: Collection<int, Vehiculo>}
     */
    public function disponibles(?string $fechaInicio, ?string $fechaFin): array
    {
        $this->servicioDisponibilidad->sincronizarEstados();

        $validador = new Validador(['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]);
        $fechaInicio = $validador->fecha('fecha_inicio', 'La fecha de inicio') ?? DisponibilidadService::hoy();
        $fechaFin = $validador->fecha('fecha_fin', 'La fecha de fin') ?? $fechaInicio;

        if (!$validador->tieneError('fecha_inicio') && !$validador->tieneError('fecha_fin') && $fechaFin < $fechaInicio) {
            $validador->agregarError('fecha_fin', 'La fecha de fin no puede ser anterior a la de inicio.');
        }

        $validador->verificar();

        $vehiculos = Vehiculo::query()
            ->where('estado', '<>', Vehiculo::MANTENIMIENTO)
            ->whereDoesntHave('reservas', function (Builder $consultaConflictos) use ($fechaInicio, $fechaFin): void {
                $this->servicioDisponibilidad->filtrarConflictos($consultaConflictos, $fechaInicio, $fechaFin);
            })
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();

        return ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin, 'vehiculos' => $vehiculos];
    }

    /**
     * @return Collection<int, Reserva>
     */
    public function historial(int $id): Collection
    {
        return $this->obtener($id)
            ->reservas()
            ->with('cliente')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param array<string, mixed> $datosVehiculo
     * @return array<string, mixed>
     */
    private function validar(array $datosVehiculo): array
    {
        $validador = new Validador($datosVehiculo);
        $anioMaximo = (int) date('Y') + 1;

        $camposValidados = [
            'marca' => $validador->texto('marca', 'La marca', 100, true),
            'modelo' => $validador->texto('modelo', 'El modelo', 100, true),
            'anio' => $validador->entero('anio', 'El año', 1901, $anioMaximo, true),
            'categoria' => $validador->texto('categoria', 'La categoría', 50),
        ];

        // "alquilado" lo asigna el sistema a partir de las reservas.
        $estado = $validador->opcion('estado', 'El estado', [Vehiculo::DISPONIBLE, Vehiculo::MANTENIMIENTO]);

        if ($estado !== null) {
            $camposValidados['estado'] = $estado;
        }

        $validador->verificar();

        return $camposValidados;
    }
}
