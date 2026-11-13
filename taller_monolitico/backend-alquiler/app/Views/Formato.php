<?php

declare(strict_types=1);

namespace App\Views;

use App\Models\Reserva;
use App\Models\Vehiculo;
use App\Services\DisponibilidadService;
use DateTimeImmutable;

/**
 * Utilidades de presentacion usadas por las plantillas.
 */
final class Formato
{
    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    private const ETIQUETAS = [
        'disponible' => 'Disponible',
        'alquilado' => 'Alquilado',
        'mantenimiento' => 'Mantenimiento',
        'activa' => 'Activa',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'vencida' => 'Vencida',
        'programada' => 'Programada',
    ];

    /** "2026-11-13" -> "13 nov 2026" */
    public static function fecha(?string $iso): string
    {
        if ($iso === null || $iso === '') {
            return '-';
        }

        $fecha = new DateTimeImmutable(substr($iso, 0, 10));

        return $fecha->format('j') . ' ' . self::MESES[(int) $fecha->format('n') - 1] . ' ' . $fecha->format('Y');
    }

    /** Dias incluidos entre dos fechas (ambos extremos cuentan). */
    public static function dias(string $inicio, string $fin): int
    {
        return (new DateTimeImmutable($inicio))->diff(new DateTimeImmutable($fin))->days + 1;
    }

    public static function etiqueta(string $estado): string
    {
        return self::ETIQUETAS[$estado] ?? $estado;
    }

    /** Etiqueta de estado coloreada (HTML ya escapado). */
    public static function badge(string $estado): string
    {
        $clase = htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
        $texto = htmlspecialchars(self::etiqueta($estado), ENT_QUOTES, 'UTF-8');

        return "<span class=\"badge badge--{$clase}\">{$texto}</span>";
    }

    /**
     * Estado visible de una reserva: distingue las activas que aun no
     * comienzan (programada) y las que pasaron su fecha sin devolucion (vencida).
     */
    public static function estadoReserva(Reserva $reserva): string
    {
        if ($reserva->estado !== Reserva::ACTIVA) {
            return $reserva->estado;
        }

        $hoy = DisponibilidadService::hoy();

        if ($reserva->fecha_fin < $hoy) {
            return 'vencida';
        }

        return $reserva->fecha_inicio > $hoy ? 'programada' : Reserva::ACTIVA;
    }

    public static function vehiculo(?Vehiculo $vehiculo): string
    {
        return $vehiculo === null ? '-' : "{$vehiculo->marca} {$vehiculo->modelo} ({$vehiculo->anio})";
    }
}
