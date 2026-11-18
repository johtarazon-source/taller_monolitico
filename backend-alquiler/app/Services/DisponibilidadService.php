<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Reserva;
use App\Models\Vehiculo;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reglas compartidas de disponibilidad:
 * - Cuando dos reservas activas de un mismo vehiculo se cruzan en fechas.
 * - El estado automatico del vehiculo (disponible / alquilado).
 *
 * Una reserva activa cuya fecha_fin ya paso sin devolucion se considera
 * "vencida": el vehiculo sigue fuera, asi que bloquea cualquier fecha.
 */
final class DisponibilidadService
{
    public static function hoy(): string
    {
        return date('Y-m-d');
    }

    /**
     * Restringe una consulta de reservas a las activas que chocan con el rango.
     *
     * @param Builder<Reserva> $consulta
     * @return Builder<Reserva>
     */
    public function filtrarConflictos(Builder $consulta, string $fechaInicio, string $fechaFin): Builder
    {
        $hoy = self::hoy();

        return $consulta
            ->where('estado', Reserva::ACTIVA)
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where(function (Builder $consultaFechas) use ($fechaInicio, $hoy): void {
                $consultaFechas->where('fecha_fin', '>=', $fechaInicio)
                    ->orWhere('fecha_fin', '<', $hoy);
            });
    }

    public function tieneConflicto(int $vehiculoId, string $fechaInicio, string $fechaFin): bool
    {
        $consulta = Reserva::query()->where('vehiculo_id', $vehiculoId);

        return $this->filtrarConflictos($consulta, $fechaInicio, $fechaFin)->exists();
    }

    /**
     * Recalcula el estado de los vehiculos que no estan en mantenimiento:
     * "alquilado" si tienen una reserva activa ya iniciada, si no "disponible".
     * Se ejecuta antes de cada consulta, asi las reservas futuras pasan a
     * alquilado solas el dia en que comienzan.
     */
    public function sincronizarEstados(): void
    {
        DB::connection()->update(
            'UPDATE vehiculos vehiculo
                SET vehiculo.estado = CASE
                    WHEN EXISTS (
                        SELECT 1 FROM reservas reserva
                         WHERE reserva.vehiculo_id = vehiculo.id
                           AND reserva.estado = ?
                           AND reserva.fecha_inicio <= ?
                    ) THEN ? ELSE ? END
              WHERE vehiculo.estado <> ?',
            [Reserva::ACTIVA, self::hoy(), Vehiculo::ALQUILADO, Vehiculo::DISPONIBLE, Vehiculo::MANTENIMIENTO]
        );
    }
}
