<?php

namespace app\controllers\exceptions;

use RuntimeException;

/**
 * Excepciones de la aplicacion. Cada una define su codigo HTTP y public/index.php las convierte
 * en la respuesta JSON de error. Este archivo lo carga autoload.php porque agrupa varias clases.
 */

/**
 * Excepcion base. Puede adjuntar un detalle de errores por campo que se envia en "data".
 */
class AppException extends RuntimeException
{
    /**
     * @param array<string, string>|null $errores
     */
    public function __construct(string $mensaje, private int $codigo_estado = 400, private ?array $errores = null)
    {
        parent::__construct($mensaje);
    }

    public function get_codigo_estado(): int
    {
        return $this->codigo_estado;
    }

    /**
     * @return array<string, string>|null
     */
    public function get_errores(): ?array
    {
        return $this->errores;
    }
}

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

/**
 * Falta el token, es invalido o esta expirado, o las credenciales son incorrectas.
 */
final class UnauthorizedException extends AppException
{
    public function __construct(string $mensaje = 'No autorizado.')
    {
        parent::__construct($mensaje, 401);
    }
}

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
