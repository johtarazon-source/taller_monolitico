<?php

namespace app\models\database;

use app\models\config\Env;
use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;

class AlquilerDb implements AlquilerDbInterface
{
    private const ERROR_BASE_DE_DATOS_INEXISTENTE = 1049;
    private const TOTAL_TABLAS = 3;

    private ?mysqli $conexion = null;

    public function select(string $sql, string $tipos = '', array $parametros = []): array
    {
        $sentencia = $this->preparar($sql, $tipos, $parametros);
        $sentencia->execute();
        $filas = $sentencia->get_result()->fetch_all(MYSQLI_ASSOC);
        $sentencia->close();

        return $filas;
    }

    public function execute(string $sql, string $tipos = '', array $parametros = []): int
    {
        $sentencia = $this->preparar($sql, $tipos, $parametros);
        $sentencia->execute();
        $afectadas = $sentencia->affected_rows;
        $sentencia->close();

        return $afectadas;
    }

    public function last_insert_id(): int
    {
        return (int) $this->conectar()->insert_id;
    }

    public function begin_transaction(): void
    {
        $this->conectar()->begin_transaction();
    }

    public function commit(): void
    {
        $this->conectar()->commit();
    }

    public function rollback(): void
    {
        $this->conectar()->rollback();
    }

    private function preparar(string $sql, string $tipos, array $parametros): mysqli_stmt
    {
        $sentencia = $this->conectar()->prepare($sql);

        if ($parametros !== []) {
            $sentencia->bind_param($tipos, ...$parametros);
        }

        return $sentencia;
    }

    private function conectar(): mysqli
    {
        if ($this->conexion === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $this->conexion = $this->abrir_conexion();
            $this->conexion->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
            $this->crear_tablas_si_faltan($this->conexion);
        }

        return $this->conexion;
    }

    private function abrir_conexion(): mysqli
    {
        $servidor = Env::obtener('DB_HOST', '127.0.0.1');
        $usuario = Env::obtener('DB_USERNAME', 'root');
        $contrasena = Env::obtener('DB_PASSWORD', '');
        $base_de_datos = Env::obtener('DB_DATABASE', 'alquiler_vehiculos_db');
        $puerto = (int) Env::obtener('DB_PORT', '3306');

        try {
            return new mysqli($servidor, $usuario, $contrasena, $base_de_datos, $puerto);
        } catch (mysqli_sql_exception $excepcion) {
            if ($excepcion->getCode() !== self::ERROR_BASE_DE_DATOS_INEXISTENTE) {
                throw $excepcion;
            }
        }

        $conexion = new mysqli($servidor, $usuario, $contrasena, '', $puerto);
        $nombre_escapado = str_replace('`', '``', $base_de_datos);
        $conexion->query("CREATE DATABASE IF NOT EXISTS `{$nombre_escapado}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conexion->select_db($base_de_datos);

        return $conexion;
    }

    private function crear_tablas_si_faltan(mysqli $conexion): void
    {
        $tablas_existentes = (int) $conexion->query(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('vehiculos', 'clientes', 'reservas')"
        )->fetch_row()[0];

        if ($tablas_existentes === self::TOTAL_TABLAS) {
            return;
        }

        $esquema = (string) file_get_contents(dirname(__DIR__, 2) . '/database/alquiler_vehiculos_db.sql');

        foreach (explode(';', $esquema) as $sentencia) {
            $sentencia = trim($sentencia);

            if ($sentencia === '' || preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $sentencia) === 1) {
                continue;
            }

            $conexion->query($sentencia);
        }
    }
}
