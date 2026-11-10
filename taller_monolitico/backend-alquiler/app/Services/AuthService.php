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
        if ($usuario !== $this->adminUser || !password_verify($password, $this->adminPasswordHash)) {
            throw new UnauthorizedException('Usuario o contraseña incorrectos.');
        }

        $token = bin2hex(random_bytes(32));
        $sesiones = $this->leerSesiones();

        $sesiones[$token] = [
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
     * Invalida un token eliminandolo del archivo de sesiones.
     */
    public function logout(string $token): void
    {
        $sesiones = $this->leerSesiones();

        if (isset($sesiones[$token])) {
            unset($sesiones[$token]);
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

        if (!isset($sesiones[$token])) {
            return false;
        }

        $sesion = $sesiones[$token];

        return ($sesion['activo'] ?? false) === true && ($sesion['expira_en'] ?? 0) > time();
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
