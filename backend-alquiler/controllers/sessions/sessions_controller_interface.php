<?php

namespace app\controllers\sessions;

interface SessionsControllerInterface
{
    public function login(array $datos): array;

    public function logout(string $token): void;

    public function validate_token(string $token): bool;
}
