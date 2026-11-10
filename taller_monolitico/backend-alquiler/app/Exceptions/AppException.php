<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Excepcion base de dominio. Las excepciones especificas de cada modulo
 * (validacion, conflicto, no encontrado, etc.) la extienden en las fases
 * siguientes e incluyen su propio codigo HTTP.
 */
class AppException extends RuntimeException
{
    public function __construct(string $mensaje, private int $statusCode = 400)
    {
        parent::__construct($mensaje);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
