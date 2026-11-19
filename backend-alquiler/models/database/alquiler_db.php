<?php

namespace app\models\database;

use app\models\config\Env;
use mysqli;
use mysqli_stmt;

class AlquilerDb implements AlquilerDbInterface
{
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

            $this->conexion = new mysqli(
                Env::obtener('DB_HOST', '127.0.0.1'),
                Env::obtener('DB_USERNAME', 'root'),
                Env::obtener('DB_PASSWORD', ''),
                Env::obtener('DB_DATABASE', 'alquiler_vehiculos_db'),
                (int) Env::obtener('DB_PORT', '3306')
            );
            $this->conexion->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        }

        return $this->conexion;
    }
}
