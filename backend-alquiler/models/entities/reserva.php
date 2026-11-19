<?php

namespace app\models\entities;

class Reserva
{
    public const ACTIVA = 'activa';
    public const COMPLETADA = 'completada';
    public const CANCELADA = 'cancelada';

    private int $id;
    private int $cliente_id;
    private int $vehiculo_id;
    private string $fecha_inicio;
    private string $fecha_fin;
    private ?string $estado;
    private ?string $created_at;
    private ?string $updated_at;
    private ?Cliente $cliente;
    private ?Vehiculo $vehiculo;

    /**
     * El cliente y el vehiculo se cargan solo cuando la consulta los necesita.
     */
    public function __construct(
        int $id,
        int $cliente_id,
        int $vehiculo_id,
        string $fecha_inicio,
        string $fecha_fin,
        ?string $estado = self::ACTIVA,
        ?string $created_at = null,
        ?string $updated_at = null,
        ?Cliente $cliente = null,
        ?Vehiculo $vehiculo = null
    ) {
        $this->id = $id;
        $this->cliente_id = $cliente_id;
        $this->vehiculo_id = $vehiculo_id;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->estado = $estado;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->cliente = $cliente;
        $this->vehiculo = $vehiculo;
    }

    /** Fecha de hoy (AAAA-MM-DD) en la zona horaria de la aplicacion. */
    public static function hoy(): string
    {
        return date('Y-m-d');
    }

    /**
     * Estado que ve el usuario: distingue las activas que aun no comienzan
     * (programada) y las que pasaron su fecha sin devolucion (vencida).
     */
    public function get_estado_visible(): ?string
    {
        if ($this->estado !== self::ACTIVA) {
            return $this->estado;
        }

        $hoy = self::hoy();

        if ($this->fecha_fin < $hoy) {
            return 'vencida';
        }

        return $this->fecha_inicio > $hoy ? 'programada' : self::ACTIVA;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_cliente_id(): int
    {
        return $this->cliente_id;
    }

    public function get_vehiculo_id(): int
    {
        return $this->vehiculo_id;
    }

    public function get_fecha_inicio(): string
    {
        return $this->fecha_inicio;
    }

    public function get_fecha_fin(): string
    {
        return $this->fecha_fin;
    }

    public function get_estado(): ?string
    {
        return $this->estado;
    }

    public function get_created_at(): ?string
    {
        return $this->created_at;
    }

    public function get_updated_at(): ?string
    {
        return $this->updated_at;
    }

    public function get_cliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function get_vehiculo(): ?Vehiculo
    {
        return $this->vehiculo;
    }
}
