<?php

namespace app\models\entities;

class Vehiculo
{
    public const DISPONIBLE = 'disponible';
    public const ALQUILADO = 'alquilado';
    public const MANTENIMIENTO = 'mantenimiento';

    private int $id;
    private string $marca;
    private string $modelo;
    private int $anio;
    private ?string $categoria;
    private ?string $estado;
    private ?string $created_at;
    private ?string $updated_at;

    public function __construct(
        int $id,
        string $marca,
        string $modelo,
        int $anio,
        ?string $categoria = null,
        ?string $estado = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->anio = $anio;
        $this->categoria = $categoria;
        $this->estado = $estado;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_marca(): string
    {
        return $this->marca;
    }

    public function get_modelo(): string
    {
        return $this->modelo;
    }

    public function get_anio(): int
    {
        return $this->anio;
    }

    public function get_categoria(): ?string
    {
        return $this->categoria;
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
}
