<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Se lanza cuando falta el token, es invalido o esta expirado.
 */
final class UnauthorizedException extends AppException
{
    public function __construct(string $mensaje = 'No autorizado.')
    {
        parent::__construct($mensaje, 401);
    }
}
