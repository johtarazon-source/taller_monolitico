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
        private string $rutaArchivoSesiones,
        private string $usuarioAdministrador,
        private string $hashContrasenaAdministrador,
        private int $minutosVigencia
    ) {
    }

    /**
     * Valida credenciales y genera un token de sesion.
     *
     * @throws UnauthorizedException si el usuario o la contraseña no coinciden
     */
    public function login(string $usuario, string $contrasena): string
    {
        if (!$this->credencialesValidas($usuario, $contrasena)) {
            throw new UnauthorizedException('Usuario o contraseña incorrectos.');
        }

        $tokenAcceso = bin2hex(random_bytes(32));
        $sesiones = $this->leerSesiones();

        $sesiones[$this->huellaToken($tokenAcceso)] = [
            'usuario' => $usuario,
            'activo' => true,
            'expira_en' => (new DateTimeImmutable())
                ->modify("+{$this->minutosVigencia} minutes")
                ->getTimestamp(),
        ];

        $this->escribirSesiones($sesiones);

        return $tokenAcceso;
    }

    /**
     * Compara con el usuario administrador definido en el .env.
     * La usan el login de la API (token) y el de la web (sesion PHP).
     */
    public function credencialesValidas(string $usuario, string $contrasena): bool
    {
        return $usuario === $this->usuarioAdministrador && password_verify($contrasena, $this->hashContrasenaAdministrador);
    }

    /**
     * Invalida un token eliminandolo del archivo de sesiones.
     */
    public function logout(string $tokenAcceso): void
    {
        $sesiones = $this->leerSesiones();
        $huellaCalculada = $this->huellaToken($tokenAcceso);

        if (isset($sesiones[$huellaCalculada])) {
            unset($sesiones[$huellaCalculada]);
            $this->escribirSesiones($sesiones);
        }
    }

    /**
     * Verifica que el token exista, este activo y no haya expirado.
     */
    public function validarToken(string $tokenAcceso): bool
    {
        if ($tokenAcceso === '') {
            return false;
        }

        $sesiones = $this->leerSesiones();
        $sesion = $sesiones[$this->huellaToken($tokenAcceso)] ?? null;

        if ($sesion === null) {
            return false;
        }

        return ($sesion['activo'] ?? false) === true && ($sesion['expira_en'] ?? 0) > time();
    }

    /**
     * Las sesiones se indexan por el hash del token (no por el token en claro)
     * para que una fuga del archivo de sesiones no exponga tokens utilizables.
     */
    private function huellaToken(string $tokenAcceso): string
    {
        return hash('sha256', $tokenAcceso);
    }

    /**
     * Lee el archivo de sesiones con bloqueo compartido.
     *
     * @return array<string, array{usuario: string, activo: bool, expira_en: int}>
     */
    private function leerSesiones(): array
    {
        if (!file_exists($this->rutaArchivoSesiones)) {
            return [];
        }

        $archivo = fopen($this->rutaArchivoSesiones, 'r');

        if ($archivo === false) {
            return [];
        }

        flock($archivo, LOCK_SH);
        $contenido = stream_get_contents($archivo);
        flock($archivo, LOCK_UN);
        fclose($archivo);

        $sesionesDecodificadas = json_decode((string) $contenido, true);

        return is_array($sesionesDecodificadas) ? $sesionesDecodificadas : [];
    }

    /**
     * Escribe el archivo de sesiones con bloqueo exclusivo.
     *
     * @param array<string, array{usuario: string, activo: bool, expira_en: int}> $sesiones
     */
    private function escribirSesiones(array $sesiones): void
    {
        $archivo = fopen($this->rutaArchivoSesiones, 'c+');

        if ($archivo === false) {
            throw new RuntimeException('No se pudo escribir el archivo de sesiones.');
        }

        flock($archivo, LOCK_EX);
        ftruncate($archivo, 0);
        rewind($archivo);
        fwrite($archivo, (string) json_encode($sesiones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        fflush($archivo);
        flock($archivo, LOCK_UN);
        fclose($archivo);
    }
}
