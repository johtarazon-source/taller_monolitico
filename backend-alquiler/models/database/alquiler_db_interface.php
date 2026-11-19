<?php

namespace app\models\database;

interface AlquilerDbInterface
{
    public function select(string $sql, string $tipos = '', array $parametros = []): array;

    public function execute(string $sql, string $tipos = '', array $parametros = []): int;

    public function last_insert_id(): int;

    public function begin_transaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
