<?php

namespace app\models\repositories\tokens_repository;

interface TokensRepositoryInterface
{
    /**
     * Guarda una sesion. La clave es la huella (sha256) del token, nunca el token en claro.
     */
    public function save_session(string $huella, string $usuario, int $expira_en): void;

    /**
     * @return array{usuario: string, activo: bool, expira_en: int}|null
     */
    public function find_session(string $huella): ?array;

    public function delete_session(string $huella): void;
}
