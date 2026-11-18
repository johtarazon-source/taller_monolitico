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
     * @param Builder<Reserva> $query
     * @return Builder<Reserva>
     */
    public function filtrarConflictos(Builder $query, string $inicio, string $fin): Builder
    {
        $hoy = self::hoy();

        return $query
            ->where('estado', Reserva::ACTIVA)
            ->where('fecha_inicio', '<=', $fin)
            ->where(function (Builder $q) use ($inicio, $hoy): void {
                $q->where('fecha_fin', '>=', $inicio)
                    ->orWhere('fecha_fin', '<', $hoy);
            });
    }

    public function tieneConflicto(int $vehiculoId, string $inicio, string $fin): bool
    {
        $query = Reserva::query()->where('vehiculo_id', $vehiculoId);

        return $this->filtrarConflictos($query, $inicio, $fin)->exists();
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
            'UPDATE vehiculos v
                SET v.estado = CASE
                    WHEN EXISTS (
                        SELECT 1 FROM reservas r
                         WHERE r.vehiculo_id = v.id
                           AND r.estado = ?
                           AND r.fecha_inicio <= ?
                    ) THEN ? ELSE ? END
              WHERE v.estado <> ?',
            [Reserva::ACTIVA, self::hoy(), Vehiculo::ALQUILADO, Vehiculo::DISPONIBLE, Vehiculo::MANTENIMIENTO]
        );
    }
}
