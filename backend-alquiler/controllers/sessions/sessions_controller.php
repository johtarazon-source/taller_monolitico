<?php

namespace app\controllers\sessions;

use app\controllers\exceptions\AppException;
use app\controllers\exceptions\UnauthorizedException;
use app\models\repositories\tokens_repository\TokensRepositoryInterface;
use DateTimeImmutable;

class SessionsController implements SessionsControllerInterface
{
    private TokensRepositoryInterface $tokens_repository;
    private string $usuario_administrador;
    private string $contrasena_administrador;
    private int $minutos_vigencia;

    public function __construct(
        TokensRepositoryInterface $tokens_repository,
        string $usuario_administrador,
        string $contrasena_administrador,
        int $minutos_vigencia
    ) {
        $this->tokens_repository = $tokens_repository;
        $this->usuario_administrador = $usuario_administrador;
        $this->contrasena_administrador = $contrasena_administrador;
        $this->minutos_vigencia = $minutos_vigencia;
    }

    public function login(array $datos): array
    {
        $usuario = trim((string) ($datos['usuario'] ?? ''));
        $contrasena = (string) ($datos['password'] ?? '');

        if ($usuario === '' || $contrasena === '') {
            throw new AppException('Usuario y contraseña son obligatorios.', 422);
        }

        $usuario_valido = hash_equals($this->usuario_administrador, $usuario);
        $contrasena_valida = $this->contrasena_administrador !== '' && hash_equals($this->contrasena_administrador, $contrasena);

        if (!$usuario_valido || !$contrasena_valida) {
            throw new UnauthorizedException('Usuario o contraseña incorrectos.');
        }

        $token = bin2hex(random_bytes(32));
        $expira_en = (new DateTimeImmutable())->modify("+{$this->minutos_vigencia} minutes")->getTimestamp();
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
