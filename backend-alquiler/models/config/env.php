<?php

namespace app\models\config;

class Env
{
    private static array $valores = [];

    public static function cargar(string $archivo): void
    {
        if (!is_file($archivo)) {
            return;
        }

        foreach (preg_split('/\r\n|\n|\r/', (string) file_get_contents($archivo)) as $linea) {
            $linea = trim($linea);

            if ($linea === '' || $linea[0] === '#') {
                continue;
            }

            if (str_starts_with($linea, 'export ')) {
                $linea = ltrim(substr($linea, 7));
            }

            $posicion = strpos($linea, '=');

            if ($posicion === false) {
                continue;
            }

            $nombre = trim(substr($linea, 0, $posicion));

            if (!preg_match('/^[A-Za-z0-9_.]+$/', $nombre) || self::leer_del_entorno($nombre) !== null) {
                continue;
            }

            self::$valores[$nombre] = self::leer_valor(ltrim(substr($linea, $posicion + 1)));
        }
    }

    public static function obtener(string $nombre, ?string $defecto = null): ?string
    {
        return self::leer_del_entorno($nombre) ?? self::$valores[$nombre] ?? $defecto;
    }

    private static function leer_del_entorno(string $nombre): ?string
    {
        $valor = getenv($nombre);

        if ($valor !== false) {
            return $valor;
        }

        foreach ([$_ENV, $_SERVER] as $origen) {
            if (isset($origen[$nombre]) && is_scalar($origen[$nombre])) {
                return (string) $origen[$nombre];
            }
        }

        return null;
    }

    private static function leer_valor(string $texto): string
    {
        if ($texto === '') {
            return '';
        }

        if ($texto[0] === "'") {
            $fin = strpos($texto, "'", 1);

            return $fin === false ? substr($texto, 1) : substr($texto, 1, $fin - 1);
        }

        if ($texto[0] === '"') {
            return self::leer_entre_comillas_dobles($texto);
        }

        preg_match('/^[^\s#]*/', $texto, $coincidencia);

        return $coincidencia[0];
    }

    private static function leer_entre_comillas_dobles(string $texto): string
    {
        $escapes = ['n' => "\n", 'r' => "\r", 't' => "\t", 'f' => "\f", 'v' => "\v", '\\' => '\\', '"' => '"', '$' => '$'];
        $resultado = '';
        $largo = strlen($texto);

        for ($posicion = 1; $posicion < $largo; $posicion++) {
            $caracter = $texto[$posicion];

            if ($caracter === '"') {
                break;
            }

            if ($caracter === '\\' && $posicion + 1 < $largo && isset($escapes[$texto[$posicion + 1]])) {
                $resultado .= $escapes[$texto[$posicion + 1]];
                $posicion++;
                continue;
            }

            $resultado .= $caracter;
        }

        return $resultado;
    }
}
