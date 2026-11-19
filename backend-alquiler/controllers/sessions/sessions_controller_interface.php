<?php

namespace app\controllers\sessions;

interface SessionsControllerInterface
{
    /**
     * Valida las credenciales del administrador y crea un token de sesion.
     *
     * @param array<string, mixed> $datos usuario y password
     * @return array{token: string, usuario: string}
     */
    public function login(array $datos): array;

    /** Invalida el token. */
    public function logout(string $token): void;

    /** Indica si el token existe, esta activo y no ha expirado. */
    public function validate_token(string $token): bool;
}
