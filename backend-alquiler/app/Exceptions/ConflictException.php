<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * La operacion choca con el estado actual de los datos
 * (reservas cruzadas, vehiculo alquilado, registros con historial, etc.).
 */
final class ConflictException extends AppException
{
    public function __construct(string $mensaje)
    {
        parent::__construct($mensaje, 409);
    }
}
