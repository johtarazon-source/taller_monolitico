<?php

namespace app\models\database;

interface AlquilerDbInterface
{
    /**
     * Ejecuta un SELECT y devuelve todas las filas.
     *
     * @param string $tipos tipos de los parametros para mysqli: "i" entero, "s" texto, "d" decimal
     * @param list<mixed> $parametros
     * @return list<array<string, mixed>>
     */
    public function select(string $sql, string $tipos = '', array $parametros = []): array;

    /**
     * Ejecuta un INSERT, UPDATE o DELETE y devuelve las filas afectadas.
     *
     * @param list<mixed> $parametros
     */
    public function execute(string $sql, string $tipos = '', array $parametros = []): int;

    public function last_insert_id(): int;

    public function begin_transaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
