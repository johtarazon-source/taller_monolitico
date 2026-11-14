<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * El recurso solicitado no existe.
 */
final class NotFoundException extends AppException
{
    public function __construct(string $mensaje = 'Recurso no encontrado.')
    {
        parent::__construct($mensaje, 404);
    }
}
