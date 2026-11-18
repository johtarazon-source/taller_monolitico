<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundException;
use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Support\Validador;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reservas, devoluciones y cancelaciones. Cada cambio recalcula el estado
 * de los vehiculos para mantenerlo coherente con las reservas.
 */
final class ReservaService
{
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    /**
     * @param array<string, mixed> $filtros estado, cliente_id, vehiculo_id
     * @return Collection<int, Reserva>
     */
    public function listar(array $filtros): Collection
    {
        $this->disponibilidad->sincronizarEstados();

        $query = Reserva::query()
            ->with(['cliente', 'vehiculo'])
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id');

        if (!empty($filtros['estado'])) {
            $query->where('estado', (string) $filtros['estado']);
        }

        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', (int) $filtros['cliente_id']);
        }

        if (!empty($filtros['vehiculo_id'])) {
            $query->where('vehiculo_id', (int) $filtros['vehiculo_id']);
        }

        return $query->get();
    }

    public function obtener(int $id): Reserva
    {
        $reserva = Reserva::with(['cliente', 'vehiculo'])->find($id);

        if ($reserva === null) {
            throw new NotFoundException('La reserva no existe.');
        }

        return $reserva;
    }

    /**
     * Crea una reserva validando fechas, cliente, vehiculo y cruces.
     *
     * @param array<string, mixed> $datos cliente_id, vehiculo_id, fecha_inicio, fecha_fin
     */
    public function crear(array $datos): Reserva
    {
        $v = new Validador($datos);
        $clienteId = $v->entero('cliente_id', 'El cliente', 1, PHP_INT_MAX, true);
        $vehiculoId = $v->entero('vehiculo_id', 'El vehículo', 1, PHP_INT_MAX, true);
        $inicio = $v->fecha('fecha_inicio', 'La fecha de inicio', true);
        $fin = $v->fecha('fecha_fin', 'La fecha de fin', true);

        if ($inicio !== null && $inicio < DisponibilidadService::hoy()) {
            $v->agregarError('fecha_inicio', 'La fecha de inicio no puede ser anterior a hoy.');
        }

        if ($inicio !== null && $fin !== null && $fin < $inicio) {
            $v->agregarError('fecha_fin', 'La fecha de fin no puede ser anterior a la de inicio.');
        }

        $cliente = $clienteId !== null ? Cliente::find($clienteId) : null;

        if ($clienteId !== null && $cliente === null) {
            $v->agregarError('cliente_id', 'El cliente no existe.');
        } elseif ($cliente !== null && trim((string) $cliente->numero_licencia) === '') {
            $v->agregarError('cliente_id', 'El cliente no tiene número de licencia registrado; no puede alquilar.');
        }

        if ($vehiculoId !== null && !Vehiculo::query()->whereKey($vehiculoId)->exists()) {
            $v->agregarError('vehiculo_id', 'El vehículo no existe.');
        }

        $v->verificar();

        $this->disponibilidad->sincronizarEstados();

        $reserva = DB::connection()->transaction(function () use ($clienteId, $vehiculoId, $inicio, $fin): Reserva {
            // Bloquea la fila del vehiculo para que dos reservas simultaneas no se crucen.
            $vehiculo = Vehiculo::query()->whereKey($vehiculoId)->lockForUpdate()->firstOrFail();

            if ($vehiculo->estado === Vehiculo::MANTENIMIENTO) {
                throw new ConflictException('El vehículo está en mantenimiento y no se puede reservar.');
            }

            if ($this->disponibilidad->tieneConflicto($vehiculoId, $inicio, $fin)) {
                throw new ConflictException('El vehículo ya tiene una reserva activa que se cruza con esas fechas.');
            }

            return Reserva::create([
                'cliente_id' => $clienteId,
                'vehiculo_id' => $vehiculoId,
                'fecha_inicio' => $inicio,
                'fecha_fin' => $fin,
                'estado' => Reserva::ACTIVA,
            ]);
        });

        $this->disponibilidad->sincronizarEstados();

        return $this->obtener($reserva->id);
    }

    /**
     * Registra la devolucion del vehiculo: la reserva queda completada y el
     * vehiculo vuelve a estar disponible (o a mantenimiento si se indica).
     */
    public function devolver(int $id, bool $enviarAMantenimiento = false): Reserva
    {
        $reserva = $this->obtenerActiva($id);

        if ($reserva->fecha_inicio > DisponibilidadService::hoy()) {
            throw new ConflictException('La reserva aún no ha comenzado; si no se va a usar, cancélela.');
        }

        DB::connection()->transaction(function () use ($reserva, $enviarAMantenimiento): void {
            $reserva->update(['estado' => Reserva::COMPLETADA]);

            if ($enviarAMantenimiento) {
                $reserva->vehiculo()->update(['estado' => Vehiculo::MANTENIMIENTO]);
            }
        });

        $this->disponibilidad->sincronizarEstados();

        return $this->obtener($id);
    }

    public function cancelar(int $id): Reserva
    {
        $reserva = $this->obtenerActiva($id);
        $reserva->update(['estado' => Reserva::CANCELADA]);

        $this->disponibilidad->sincronizarEstados();

        return $this->obtener($id);
    }

    private function obtenerActiva(int $id): Reserva
    {
        $reserva = $this->obtener($id);

        if ($reserva->estado !== Reserva::ACTIVA) {
            throw new ConflictException("La reserva ya está {$reserva->estado}.");
        }

        return $reserva;
    }
}
