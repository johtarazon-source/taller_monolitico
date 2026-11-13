<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;
use App\Models\Reserva;
use App\Models\Vehiculo;

/**
 * Reporte basico para el panel principal: estado de la flota y reservas
 * que requieren atencion.
 */
final class ReporteService
{
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function resumen(): array
    {
        $this->disponibilidad->sincronizarEstados();
        $hoy = DisponibilidadService::hoy();

        $porEstado = Vehiculo::query()
            ->selectRaw('estado, COUNT(*) AS total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $activas = Reserva::query()->with(['cliente', 'vehiculo'])->where('estado', Reserva::ACTIVA);

        return [
            'fecha' => $hoy,
            'vehiculos' => [
                'total' => (int) $porEstado->sum(),
                Vehiculo::DISPONIBLE => (int) ($porEstado[Vehiculo::DISPONIBLE] ?? 0),
                Vehiculo::ALQUILADO => (int) ($porEstado[Vehiculo::ALQUILADO] ?? 0),
                Vehiculo::MANTENIMIENTO => (int) ($porEstado[Vehiculo::MANTENIMIENTO] ?? 0),
            ],
            'clientes' => Cliente::query()->count(),
            'reservas_activas' => (clone $activas)->count(),
            // Activas cuya fecha de fin ya paso sin registrar la devolucion.
            'vencidas' => (clone $activas)->where('fecha_fin', '<', $hoy)->orderBy('fecha_fin')->get(),
            // Devoluciones esperadas hoy o en los proximos 7 dias.
            'proximas_devoluciones' => (clone $activas)
                ->where('fecha_inicio', '<=', $hoy)
                ->whereBetween('fecha_fin', [$hoy, date('Y-m-d', strtotime('+7 days'))])
                ->orderBy('fecha_fin')
                ->get(),
            // Reservas que comienzan en los proximos 7 dias.
            'proximas_salidas' => (clone $activas)
                ->where('fecha_inicio', '>', $hoy)
                ->where('fecha_inicio', '<=', date('Y-m-d', strtotime('+7 days')))
                ->orderBy('fecha_inicio')
                ->get(),
        ];
    }
}
