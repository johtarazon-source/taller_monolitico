<?php

namespace app\models\repositories\tokens_repository;

interface TokensRepositoryInterface
{
    public function save_session(string $huella, string $usuario, int $expira_en): void;

    public function find_session(string $huella): ?array;

    public function delete_session(string $huella): void;
}
