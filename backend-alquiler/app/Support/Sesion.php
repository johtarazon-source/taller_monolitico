<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Envoltura de la sesion PHP de la interfaz web: usuario autenticado,
 * mensajes flash (se muestran una sola vez) y token CSRF de los formularios.
 */
final class Sesion
{
    private const USUARIO = 'usuario';
    private const FLASH = 'flash';
    private const CSRF = 'csrf';

    public static function iniciar(string $rutaCookie): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('alquiler_sesion');
        session_set_cookie_params([
            'path' => $rutaCookie === '' ? '/' : $rutaCookie,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function usuario(): ?string
    {
        return $_SESSION[self::USUARIO] ?? null;
    }

    public static function autenticar(string $usuario): void
    {
        // Nuevo id de sesion al iniciar para evitar fijacion de sesion.
        session_regenerate_id(true);
        $_SESSION[self::USUARIO] = $usuario;
    }

    public static function cerrar(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
    }

    /**
     * @param 'exito'|'error' $tipo
     */
    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION[self::FLASH][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    /**
     * @return list<array{tipo: string, mensaje: string}>
     */
    public static function consumirFlash(): array
    {
        $mensajes = $_SESSION[self::FLASH] ?? [];
        unset($_SESSION[self::FLASH]);

        return $mensajes;
    }

    public static function tokenCsrf(): string
    {
        $_SESSION[self::CSRF] ??= bin2hex(random_bytes(32));

        return $_SESSION[self::CSRF];
    }

    public static function csrfValido(string $token): bool
    {
        return isset($_SESSION[self::CSRF]) && hash_equals($_SESSION[self::CSRF], $token);
    }
}
