<?php

namespace app\controllers\validators;

use app\controllers\exceptions\ValidationException;
use DateTimeImmutable;

class Validator
{
    private array $errores = [];

    public function __construct(private array $datos_entrada)
    {
    }

    public function texto(string $campo, string $etiqueta, int $maximo, bool $requerido = false): ?string
    {
        $valor = $this->datos_entrada[$campo] ?? null;
        $valor = is_scalar($valor) ? trim((string) $valor) : '';

        if ($valor === '') {
            if ($requerido) {
                $this->errores[$campo] = "{$etiqueta} es un campo obligatorio.";
            }

            return null;
        }

        if (mb_strlen($valor) > $maximo) {
            $this->errores[$campo] = "{$etiqueta} no puede superar {$maximo} caracteres.";
        }

        return $valor;
    }

    public function correo(string $campo, string $etiqueta, int $maximo): ?string
    {
        $valor = $this->texto($campo, $etiqueta, $maximo);

        if ($valor !== null && filter_var($valor, FILTER_VALIDATE_EMAIL) === false) {
            $this->errores[$campo] = "{$etiqueta} no tiene un formato válido.";
        }

        return $valor;
    }

    public function entero(string $campo, string $etiqueta, int $minimo, int $maximo, bool $requerido = false): ?int
    {
        $valor = $this->datos_entrada[$campo] ?? null;

        if ($valor === null || $valor === '') {
            if ($requerido) {
                $this->errores[$campo] = "{$etiqueta} es un campo obligatorio.";
            }

            return null;
        }

        $entero = filter_var($valor, FILTER_VALIDATE_INT);

        if ($entero === false || $entero < $minimo || $entero > $maximo) {
            $this->errores[$campo] = "{$etiqueta} debe ser un número entre {$minimo} y {$maximo}.";

            return null;
        }

        return $entero;
    }

    public function opcion(string $campo, string $etiqueta, array $permitidos, bool $requerido = false): ?string
    {
        $valor = $this->texto($campo, $etiqueta, 50, $requerido);

        if ($valor !== null && !in_array($valor, $permitidos, true)) {
            $this->errores[$campo] = "{$etiqueta} debe ser uno de: " . implode(', ', $permitidos) . '.';

            return null;
        }

        return $valor;
    }

    public function fecha(string $campo, string $etiqueta, bool $requerido = false): ?string
    {
        $valor = $this->texto($campo, $etiqueta, 10, $requerido);

        if ($valor === null) {
            return null;
        }

        if (!self::es_fecha_valida($valor)) {
            $this->errores[$campo] = "{$etiqueta} debe tener el formato AAAA-MM-DD.";

            return null;
        }

        return $valor;
    }

    public function agregar_error(string $campo, string $mensaje): void
    {
        $this->errores[$campo] = $mensaje;
    }

    public function tiene_error(string $campo): bool
    {
        return isset($this->errores[$campo]);
    }

    public function verificar(): void
    {
        if ($this->errores !== []) {
            throw new ValidationException($this->errores);
        }
    }

    private static function es_fecha_valida(string $valor): bool
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);

        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }
}
