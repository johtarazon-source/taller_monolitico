<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\ValidationException;
use DateTimeImmutable;

/**
 * Validador minimo de entradas. Acumula los errores por campo y los lanza
 * juntos para que el formulario pueda mostrarlos todos a la vez.
 */
final class Validador
{
    /** @var array<string, string> */
    private array $errores = [];

    /**
     * @param array<string, mixed> $datos
     */
    public function __construct(private array $datos)
    {
    }

    public function texto(string $campo, string $etiqueta, int $max, bool $requerido = false): ?string
    {
        $valor = $this->datos[$campo] ?? null;
        $valor = is_scalar($valor) ? trim((string) $valor) : '';

        if ($valor === '') {
            if ($requerido) {
                $this->errores[$campo] = "{$etiqueta} es un campo obligatorio.";
            }

            return null;
        }

        if (mb_strlen($valor) > $max) {
            $this->errores[$campo] = "{$etiqueta} no puede superar {$max} caracteres.";
        }

        return $valor;
    }

    public function correo(string $campo, string $etiqueta, int $max): ?string
    {
        $valor = $this->texto($campo, $etiqueta, $max);

        if ($valor !== null && filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
            $this->errores[$campo] = "{$etiqueta} no tiene un formato válido.";
        }

        return $valor;
    }

    public function entero(string $campo, string $etiqueta, int $min, int $max, bool $requerido = false): ?int
    {
        $valor = $this->datos[$campo] ?? null;

        if ($valor === null || $valor === '') {
            if ($requerido) {
                $this->errores[$campo] = "{$etiqueta} es un campo obligatorio.";
            }

            return null;
        }

        $entero = filter_var($valor, FILTER_VALIDATE_INT);

        if ($entero === false || $entero < $min || $entero > $max) {
            $this->errores[$campo] = "{$etiqueta} debe ser un número entre {$min} y {$max}.";

            return null;
        }

        return $entero;
    }

    /**
     * @param list<string> $permitidos
     */
    public function opcion(string $campo, string $etiqueta, array $permitidos, bool $requerido = false): ?string
    {
        $valor = $this->texto($campo, $etiqueta, 50, $requerido);

        if ($valor !== null && !in_array($valor, $permitidos, true)) {
            $this->errores[$campo] = "{$etiqueta} debe ser uno de: " . implode(', ', $permitidos) . '.';

            return null;
        }

        return $valor;
    }

    /**
     * Valida una fecha con formato estricto AAAA-MM-DD.
     */
    public function fecha(string $campo, string $etiqueta, bool $requerido = false): ?string
    {
        $valor = $this->texto($campo, $etiqueta, 10, $requerido);

        if ($valor === null) {
            return null;
        }

        if (!self::esFechaValida($valor)) {
            $this->errores[$campo] = "{$etiqueta} debe tener el formato AAAA-MM-DD.";

            return null;
        }

        return $valor;
    }

    public function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo] = $mensaje;
    }

    public function tieneError(string $campo): bool
    {
        return isset($this->errores[$campo]);
    }

    /**
     * @throws ValidationException si se acumulo algun error
     */
    public function verificar(): void
    {
        if ($this->errores !== []) {
            throw new ValidationException($this->errores);
        }
    }

    public static function esFechaValida(string $valor): bool
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }
}
