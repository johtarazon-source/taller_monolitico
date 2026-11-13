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
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    /**
     * @param array<string, mixed> $filtros estado, categoria, buscar
     * @return Collection<int, Vehiculo>
     */
    public function listar(array $filtros): Collection
    {
        $this->disponibilidad->sincronizarEstados();

        $query = Vehiculo::query()->orderBy('marca')->orderBy('modelo');

        if (!empty($filtros['estado'])) {
            $query->where('estado', (string) $filtros['estado']);
        }

        if (!empty($filtros['categoria'])) {
            $query->where('categoria', (string) $filtros['categoria']);
        }

        if (!empty($filtros['buscar'])) {
            $texto = '%' . trim((string) $filtros['buscar']) . '%';
            $query->where(function (Builder $q) use ($texto): void {
                $q->where('marca', 'like', $texto)
                    ->orWhere('modelo', 'like', $texto)
                    ->orWhere('categoria', 'like', $texto);
            });
        }

        return $query->get();
    }

    public function obtener(int $id): Vehiculo
    {
        $this->disponibilidad->sincronizarEstados();

        $vehiculo = Vehiculo::find($id);

        if ($vehiculo === null) {
            throw new NotFoundException('El vehículo no existe.');
        }

        return $vehiculo;
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function crear(array $datos): Vehiculo
    {
        $valores = $this->validar($datos);
        $valores['estado'] ??= Vehiculo::DISPONIBLE;

        return Vehiculo::create($valores)->refresh();
    }

    /**
     * @param array<string, mixed> $datos
     */
    public function actualizar(int $id, array $datos): Vehiculo
    {
        $vehiculo = $this->obtener($id);
        $valores = $this->validar($datos);

        if (($valores['estado'] ?? null) === Vehiculo::MANTENIMIENTO && $vehiculo->estado === Vehiculo::ALQUILADO) {
            throw new ConflictException('El vehículo está alquilado. Registre la devolución antes de enviarlo a mantenimiento.');
        }

        // Si se envia "disponible" estando alquilado, la sincronizacion lo deja en alquilado.
        $vehiculo->fill($valores)->save();
        $this->disponibilidad->sincronizarEstados();

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
    public function disponibles(?string $inicio, ?string $fin): array
    {
        $this->disponibilidad->sincronizarEstados();

        $validador = new Validador(['fecha_inicio' => $inicio, 'fecha_fin' => $fin]);
        $inicio = $validador->fecha('fecha_inicio', 'La fecha de inicio') ?? DisponibilidadService::hoy();
        $fin = $validador->fecha('fecha_fin', 'La fecha de fin') ?? $inicio;

        if (!$validador->tieneError('fecha_inicio') && !$validador->tieneError('fecha_fin') && $fin < $inicio) {
            $validador->agregarError('fecha_fin', 'La fecha de fin no puede ser anterior a la de inicio.');
        }

        $validador->verificar();

        $vehiculos = Vehiculo::query()
            ->where('estado', '<>', Vehiculo::MANTENIMIENTO)
            ->whereDoesntHave('reservas', function (Builder $q) use ($inicio, $fin): void {
                $this->disponibilidad->filtrarConflictos($q, $inicio, $fin);
            })
            ->orderBy('marca')
            ->orderBy('modelo')
            ->get();

        return ['fecha_inicio' => $inicio, 'fecha_fin' => $fin, 'vehiculos' => $vehiculos];
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
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    private function validar(array $datos): array
    {
        $v = new Validador($datos);
        $anioMax = (int) date('Y') + 1;

        $valores = [
            'marca' => $v->texto('marca', 'La marca', 100, true),
            'modelo' => $v->texto('modelo', 'El modelo', 100, true),
            'anio' => $v->entero('anio', 'El año', 1901, $anioMax, true),
            'categoria' => $v->texto('categoria', 'La categoría', 50),
        ];

        // "alquilado" lo asigna el sistema a partir de las reservas.
        $estado = $v->opcion('estado', 'El estado', [Vehiculo::DISPONIBLE, Vehiculo::MANTENIMIENTO]);

        if ($estado !== null) {
            $valores['estado'] = $estado;
        }

        $v->verificar();

        return $valores;
    }
}
