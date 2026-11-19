<?php

namespace app\controllers\sessions;

use app\controllers\exceptions\AppException;
use app\controllers\exceptions\UnauthorizedException;
use app\models\repositories\tokens_repository\TokensRepositoryInterface;

/**
 * Autenticacion del unico usuario administrador (definido en .env) mediante tokens aleatorios.
 * En el archivo de sesiones solo se guarda la huella (sha256) del token, para que una fuga del
 * archivo no exponga tokens utilizables.
 */
class SessionsController implements SessionsControllerInterface
{
    private TokensRepositoryInterface $tokens_repository;
    private string $usuario_administrador;
    private string $hash_contrasena_administrador;
    private int $minutos_vigencia;

    public function __construct(
        TokensRepositoryInterface $tokens_repository,
        string $usuario_administrador,
        string $hash_contrasena_administrador,
        int $minutos_vigencia
    ) {
        $this->tokens_repository = $tokens_repository;
        $this->usuario_administrador = $usuario_administrador;
        $this->hash_contrasena_administrador = $hash_contrasena_administrador;
        $this->minutos_vigencia = $minutos_vigencia;
    }

    public function login(array $datos): array
    {
        $usuario = trim((string) ($datos['usuario'] ?? ''));
        $contrasena = (string) ($datos['password'] ?? '');

        if ($usuario === '' || $contrasena === '') {
            throw new AppException('Usuario y contraseña son obligatorios.', 422);
        }

        if ($usuario !== $this->usuario_administrador || !password_verify($contrasena, $this->hash_contrasena_administrador)) {
            throw new UnauthorizedException('Usuario o contraseña incorrectos.');
        }

        $token = bin2hex(random_bytes(32));
        $expira_en = (new \DateTimeImmutable())->modify("+{$this->minutos_vigencia} minutes")->getTimestamp();
        $this->tokens_repository->save_session($this->huella($token), $usuario, $expira_en);

        return ['token' => $token, 'usuario' => $usuario];
    }

    public function logout(string $token): void
    {
        $this->tokens_repository->delete_session($this->huella($token));
    }

    public function validate_token(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $sesion = $this->tokens_repository->find_session($this->huella($token));

        return $sesion !== null && ($sesion['activo'] ?? false) === true && ($sesion['expira_en'] ?? 0) > time();
    }

    private function huella(string $token): string
    {
        return hash('sha256', $token);
    }
}
