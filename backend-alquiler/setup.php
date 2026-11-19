<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

const USUARIO_POR_DEFECTO = 'Admin';
const LONGITUD_MINIMA_CONTRASENA = 8;

function salir(string $mensaje, int $codigo = 1): never
{
    fwrite($codigo === 0 ? STDOUT : STDERR, $mensaje . PHP_EOL);
    exit($codigo);
}

function preguntar(string $pregunta, string $defecto = ''): string
{
    echo $defecto === '' ? "{$pregunta}: " : "{$pregunta} [{$defecto}]: ";
    $respuesta = fgets(STDIN);

    if ($respuesta === false) {
        salir(PHP_EOL . 'No se recibió respuesta; operación cancelada.');
    }

    $respuesta = trim($respuesta);

    return $respuesta === '' ? $defecto : $respuesta;
}

function reemplazar_o_agregar(string $contenido, string $nombre, string $valor): string
{
    $linea = "{$nombre}={$valor}";
    $patron = '/^' . preg_quote($nombre, '/') . '=.*$/m';

    if (preg_match($patron, $contenido) === 1) {
        return (string) preg_replace_callback($patron, fn (): string => $linea, $contenido, 1);
    }

    return rtrim($contenido) . PHP_EOL . $linea . PHP_EOL;
}

function eliminar_variable(string $contenido, string $nombre): string
{
    return (string) preg_replace('/^' . preg_quote($nombre, '/') . '=.*(?:\r?\n|$)/m', '', $contenido);
}

function entre_comillas_dobles(string $valor): string
{
    return '"' . strtr($valor, ['\\' => '\\\\', '"' => '\\"', '$' => '\\$']) . '"';
}

function pedir_usuario(): string
{
    while (true) {
        $usuario = preguntar('Usuario administrador', USUARIO_POR_DEFECTO);

        if (preg_match('/^[A-Za-z0-9_.@-]{3,50}$/', $usuario) === 1) {
            return $usuario;
        }

        echo 'Use de 3 a 50 caracteres: letras, números, guion, guion bajo, punto o arroba.' . PHP_EOL;
    }
}

function pedir_contrasena(): string
{
    while (true) {
        $contrasena = preguntar('Contraseña (de al menos ' . LONGITUD_MINIMA_CONTRASENA . ' caracteres; se verá en pantalla)');

        if (mb_strlen($contrasena) < LONGITUD_MINIMA_CONTRASENA) {
            echo 'La contraseña debe tener al menos ' . LONGITUD_MINIMA_CONTRASENA . ' caracteres.' . PHP_EOL;
            continue;
        }

        if ($contrasena !== preguntar('Repita la contraseña')) {
            echo 'Las contraseñas no coinciden.' . PHP_EOL;
            continue;
        }

        return $contrasena;
    }
}

$ruta_env = $argv[1] ?? __DIR__ . '/.env';
$ruta_ejemplo = __DIR__ . '/.env.example';

if (is_file($ruta_env)) {
    $respuesta = strtolower(preguntar("Ya existe {$ruta_env}. ¿Actualizar solo ADMIN_USER y ADMIN_PASSWORD? (s/N)", 'n'));

    if ($respuesta !== 's') {
        salir('No se modificó nada.', 0);
    }

    $contenido = (string) file_get_contents($ruta_env);
} else {
    if (!is_file($ruta_ejemplo)) {
        salir('No se encontró .env.example para usarlo como base.');
    }

    $contenido = (string) file_get_contents($ruta_ejemplo);
}

$usuario = pedir_usuario();
$contrasena = pedir_contrasena();

$contenido = reemplazar_o_agregar($contenido, 'ADMIN_USER', $usuario);
$contenido = reemplazar_o_agregar($contenido, 'ADMIN_PASSWORD', entre_comillas_dobles($contrasena));
$contenido = eliminar_variable($contenido, 'ADMIN_PASSWORD_HASH');

if (@file_put_contents($ruta_env, $contenido, LOCK_EX) === false) {
    salir("No se pudo escribir {$ruta_env}.");
}

salir("Listo: {$ruta_env} configurado para el usuario {$usuario}." . PHP_EOL . 'Aviso: la contraseña queda guardada en texto plano en ese archivo.', 0);
