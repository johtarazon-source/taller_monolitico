<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UnauthorizedException;
use DateTimeImmutable;
use RuntimeException;

/**
 * Gestiona la autenticacion del unico usuario administrador (definido en .env)
 * mediante tokens aleatorios persistidos en storage/sessions.json.
 */
final class AuthService
{
    public function __construct(
        private string $sessionsPath,
        private string $adminUser,
        private string $adminPasswordHash,
        private int $ttlMinutes
    ) {
    }

    /**
     * Valida credenciales y genera un token de sesion.
     *
     * @throws UnauthorizedException si el usuario o la contraseña no coinciden
     */
    public function login(string $usuario, string $password): string
    {
        if (!$this->credencialesValidas($usuario, $password)) {
            throw new UnauthorizedException('Usuario o contraseña incorrectos.');
        }

        $token = bin2hex(random_bytes(32));
        $sesiones = $this->leerSesiones();

        $sesiones[$this->hashToken($token)] = [
            'usuario' => $usuario,
            'activo' => true,
            'expira_en' => (new DateTimeImmutable())
                ->modify("+{$this->ttlMinutes} minutes")
                ->getTimestamp(),
        ];

        $this->escribirSesiones($sesiones);

        return $token;
    }

    /**
     * Compara con el usuario administrador definido en el .env.
     * La usan el login de la API (token) y el de la web (sesion PHP).
     */
    public function credencialesValidas(string $usuario, string $password): bool
    {
        return $usuario === $this->adminUser && password_verify($password, $this->adminPasswordHash);
    }

    /**
     * Invalida un token eliminandolo del archivo de sesiones.
     */
    public function logout(string $token): void
    {
        $sesiones = $this->leerSesiones();
        $hash = $this->hashToken($token);

        if (isset($sesiones[$hash])) {
            unset($sesiones[$hash]);
            $this->escribirSesiones($sesiones);
        }
    }

    /**
     * Verifica que el token exista, este activo y no haya expirado.
     */
    public function validarToken(string $token): bool
    {
        if ($token === '') {
            return false;
        }

        $sesiones = $this->leerSesiones();
        $sesion = $sesiones[$this->hashToken($token)] ?? null;

        if ($sesion === null) {
            return false;
        }

        return ($sesion['activo'] ?? false) === true && ($sesion['expira_en'] ?? 0) > time();
    }

    /**
     * Las sesiones se indexan por el hash del token (no por el token en claro)
     * para que una fuga del archivo de sesiones no exponga tokens utilizables.
     */
    private function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Lee el archivo de sesiones con bloqueo compartido.
     *
     * @return array<string, array{usuario: string, activo: bool, expira_en: int}>
     */
    private function leerSesiones(): array
    {
        if (!file_exists($this->sessionsPath)) {
            return [];
        }

        $handle = fopen($this->sessionsPath, 'r');

        if ($handle === false) {
            return [];
        }

        flock($handle, LOCK_SH);
        $contenido = stream_get_contents($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        $datos = json_decode((string) $contenido, true);

        return is_array($datos) ? $datos : [];
    }

    /**
     * Escribe el archivo de sesiones con bloqueo exclusivo.
     *
     * @param array<string, array{usuario: string, activo: bool, expira_en: int}> $sesiones
     */
    private function escribirSesiones(array $sesiones): void
    {
        $handle = fopen($this->sessionsPath, 'c+');

        if ($handle === false) {
            throw new RuntimeException('No se pudo escribir el archivo de sesiones.');
        }

        flock($handle, LOCK_EX);
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, (string) json_encode($sesiones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}
