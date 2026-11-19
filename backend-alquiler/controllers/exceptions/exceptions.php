<?php

namespace app\controllers\exceptions;

use RuntimeException;

class AppException extends RuntimeException
{
    public function __construct(string $mensaje, private int $codigo_estado = 400, private ?array $errores = null)
    {
        parent::__construct($mensaje);
    }

    public function get_codigo_estado(): int
    {
        return $this->codigo_estado;
    }

    public function get_errores(): ?array
    {
        return $this->errores;
    }
}

final class ConflictException extends AppException
{
    public function __construct(string $mensaje)
    {
        parent::__construct($mensaje, 409);
    }
}

final class NotFoundException extends AppException
{
    public function __construct(string $mensaje = 'Recurso no encontrado.')
    {
        parent::__construct($mensaje, 404);
    }
}

final class UnauthorizedException extends AppException
{
    public function __construct(string $mensaje = 'No autorizado.')
    {
        parent::__construct($mensaje, 401);
    }
}

final class ValidationException extends AppException
{
    public function __construct(array $errores, string $mensaje = 'Hay datos inválidos en la solicitud.')
    {
        parent::__construct($mensaje, 422, $errores);
    }
}
