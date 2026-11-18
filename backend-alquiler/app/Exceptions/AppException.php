<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Excepcion base de dominio. Cada subclase define su codigo HTTP y puede
 * adjuntar un detalle de errores por campo que se envia en "data".
 */
class AppException extends RuntimeException
{
    /**
     * @param array<string, string>|null $errores
     */
    public function __construct(string $mensaje, private int $codigoEstado = 400, private ?array $errores = null)
    {
        parent::__construct($mensaje);
    }

    public function obtenerCodigoEstado(): int
    {
        return $this->codigoEstado;
    }

    /**
     * @return array<string, string>|null
     */
    public function obtenerErrores(): ?array
    {
        return $this->errores;
    }
}
