<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Datos de entrada invalidos. Incluye el detalle de errores por campo.
 */
final class ValidationException extends AppException
{
    /**
     * @param array<string, string> $errores
     */
    public function __construct(array $errores, string $mensaje = 'Hay datos inválidos en la solicitud.')
    {
        parent::__construct($mensaje, 422, $errores);
    }
}
