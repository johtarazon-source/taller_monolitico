<?php

/**
 * Punto de entrada de la API (JSON). Todas las rutas empiezan con /api; cualquier otra URL
 * responde 404. Las respuestas tienen la forma { "ok": bool, "mensaje": string, "data": mixed }.
 *
 * Secciones: funciones de apoyo (respuesta, CORS, token, cuerpo), tabla de rutas y atencion
 * de la peticion.
 */

use app\controllers\config\ControllersConfig;
use app\controllers\exceptions\AppException;
use app\controllers\exceptions\UnauthorizedException;
use app\models\config\Env;

require __DIR__ . '/../autoload.php';

Env::cargar(__DIR__ . '/../.env');
date_default_timezone_set(Env::obtener('APP_TIMEZONE', 'America/Bogota'));

$mostrar_detalles_error = Env::obtener('APP_ENV', 'production') !== 'production';

// La aplicacion puede estar en una subcarpeta de Apache (XAMPP) o servirse con `php -S`.
$ruta_base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$ruta_cruda = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$es_api = str_starts_with($ruta_cruda, $ruta_base . '/api');

/* ---------------------------------------------------------------------------
 * Funciones de apoyo
 * ------------------------------------------------------------------------- */

/** Escribe la respuesta JSON estandar y termina. */
function responder(int $codigo_estado, bool $ok, string $mensaje, mixed $datos = null): never
{
    http_response_code($codigo_estado);
    header('Content-Type: application/json');
    echo (string) json_encode(['ok' => $ok, 'mensaje' => $mensaje, 'data' => $datos], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * CORS: permite que el frontend, si esta en otro origen, llame a la API.
 * CORS_ALLOWED_ORIGIN (.env) admite "*" o una lista de origenes separados por coma;
 * el origen de la peticion debe coincidir exactamente.
 */
function aplicar_cors(): void
{
    $origenes_permitidos = array_map('trim', explode(',', Env::obtener('CORS_ALLOWED_ORIGIN', '')));
    $origen = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array('*', $origenes_permitidos, true)) {
        header('Access-Control-Allow-Origin: *');
    } elseif ($origen !== '' && in_array($origen, $origenes_permitidos, true)) {
        header('Access-Control-Allow-Origin: ' . $origen);
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
}

/**
 * Token Bearer de la cabecera Authorization. Con $exigir comprueba que sea valido.
 *
 * @throws UnauthorizedException si se exige y el token falta, es invalido o expiro
 */
function leer_token(bool $exigir): string
{
    $cabecera = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_starts_with($cabecera, 'Bearer ') ? substr($cabecera, 7) : '';

    if ($exigir && !ControllersConfig::get_sessions_controller()->validate_token($token)) {
        throw new UnauthorizedException('Token invalido o expirado.');
    }

    return $token;
}

/**
 * Cuerpo de la peticion como arreglo. Acepta JSON y formularios (x-www-form-urlencoded).
 * Solo se llama en las rutas que necesitan el cuerpo.
 *
 * @return array<string, mixed>
 * @throws AppException si hay contenido pero no se pudo interpretar
 */
function leer_cuerpo(): array
{
    $crudo = (string) file_get_contents('php://input');
    $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    $interpretado = null;

    if ($tipo === 'application/json' || str_ends_with($tipo, '+json')) {
        $datos = json_decode($crudo, true);
        $interpretado = is_array($datos) ? $datos : null;
    } elseif ($tipo === 'application/x-www-form-urlencoded') {
        parse_str($crudo, $interpretado);
    }

    if ($interpretado === null && trim($crudo) !== '') {
        throw new AppException('El cuerpo de la solicitud no es un JSON válido (UTF-8).', 400);
    }

    return (array) $interpretado;
}

/* ---------------------------------------------------------------------------
 * Rutas de la API (relativas a /api)
 *
 * Cada ruta: [metodo, ruta, requiere_token, funcion]
 *   - "{id}" captura un numero.
 *   - La funcion recibe ($ids, $consulta, $cuerpo, $token) y devuelve [codigo_http, mensaje, datos].
 *   - $cuerpo es una funcion: el cuerpo solo se lee (y valida) si la ruta la llama.
 * ------------------------------------------------------------------------- */

$sesiones = fn () => ControllersConfig::get_sessions_controller();
$vehiculos = fn () => ControllersConfig::get_vehiculos_controller();
$clientes = fn () => ControllersConfig::get_clientes_controller();
$reservas = fn () => ControllersConfig::get_reservas_controller();
$reportes = fn () => ControllersConfig::get_reportes_controller();

$rutas = [
    ['POST', '/auth/login', false, fn ($ids, $q, $cuerpo) => [200, 'Inicio de sesión exitoso.', $sesiones()->login($cuerpo())]],
    ['POST', '/auth/logout', true, fn ($ids, $q, $cuerpo, $token) => [200, 'Sesión cerrada correctamente.', $sesiones()->logout($token)]],

    ['GET', '/resumen', true, fn () => [200, 'Resumen obtenido.', $reportes()->get_resumen()]],

    // "disponibles" va antes de "{id}" para que no se confunda con un id.
    ['GET', '/vehiculos/disponibles', true, fn ($ids, $q) => [200, 'Vehículos disponibles obtenidos.',
        $vehiculos()->get_vehiculos_disponibles($q['fecha_inicio'] ?? null, $q['fecha_fin'] ?? null)]],
    ['GET', '/vehiculos', true, fn ($ids, $q) => [200, 'Vehículos obtenidos.', $vehiculos()->get_vehiculos_all($q)]],
    ['POST', '/vehiculos', true, fn ($ids, $q, $cuerpo) => [201, 'Vehículo registrado correctamente.', $vehiculos()->save_vehiculo($cuerpo())]],
    ['GET', '/vehiculos/{id}', true, fn ($ids) => [200, 'Vehículo obtenido.', $vehiculos()->get_vehiculo($ids[0])]],
    ['PUT', '/vehiculos/{id}', true, fn ($ids, $q, $cuerpo) => [200, 'Vehículo actualizado correctamente.', $vehiculos()->update_vehiculo($ids[0], $cuerpo())]],
    ['DELETE', '/vehiculos/{id}', true, fn ($ids) => [200, 'Vehículo eliminado correctamente.', $vehiculos()->delete_vehiculo($ids[0])]],
    ['GET', '/vehiculos/{id}/historial', true, fn ($ids) => [200, 'Historial del vehículo obtenido.', $vehiculos()->get_historial($ids[0])]],

    ['GET', '/clientes', true, fn ($ids, $q) => [200, 'Clientes obtenidos.', $clientes()->get_clientes_all($q['buscar'] ?? null)]],
    ['POST', '/clientes', true, fn ($ids, $q, $cuerpo) => [201, 'Cliente registrado correctamente.', $clientes()->save_cliente($cuerpo())]],
    ['GET', '/clientes/{id}', true, fn ($ids) => [200, 'Cliente obtenido.', $clientes()->get_cliente($ids[0])]],
    ['PUT', '/clientes/{id}', true, fn ($ids, $q, $cuerpo) => [200, 'Cliente actualizado correctamente.', $clientes()->update_cliente($ids[0], $cuerpo())]],
    ['DELETE', '/clientes/{id}', true, fn ($ids) => [200, 'Cliente eliminado correctamente.', $clientes()->delete_cliente($ids[0])]],
    ['GET', '/clientes/{id}/historial', true, fn ($ids) => [200, 'Historial del cliente obtenido.', $clientes()->get_historial($ids[0])]],

    ['GET', '/reservas', true, fn ($ids, $q) => [200, 'Reservas obtenidas.', $reservas()->get_reservas_all($q)]],
    ['POST', '/reservas', true, fn ($ids, $q, $cuerpo) => [201, 'Reserva registrada correctamente.', $reservas()->save_reserva($cuerpo())]],
    ['GET', '/reservas/{id}', true, fn ($ids) => [200, 'Reserva obtenida.', $reservas()->get_reserva($ids[0])]],
    ['POST', '/reservas/{id}/devolucion', true, fn ($ids, $q, $cuerpo) => [200, 'Devolución registrada correctamente.',
        $reservas()->return_reserva($ids[0], filter_var($cuerpo()['mantenimiento'] ?? false, FILTER_VALIDATE_BOOLEAN))]],
    ['POST', '/reservas/{id}/cancelacion', true, fn ($ids) => [200, 'Reserva cancelada correctamente.', $reservas()->cancel_reserva($ids[0])]],
];

/* ---------------------------------------------------------------------------
 * Atencion de la peticion
 * ------------------------------------------------------------------------- */

try {
    if ($es_api) {
        aplicar_cors();
    }

    // Ruta relativa a /api: "/taller/.../public/api/vehiculos/5" -> "/vehiculos/5"
    $ruta_decodificada = rawurldecode($ruta_cruda);
    $prefijo_api = $ruta_base . '/api';
    $ruta = null;

    if ($es_api && str_starts_with($ruta_decodificada, $prefijo_api . '/') && strlen($ruta_decodificada) > strlen($prefijo_api . '/')) {
        $ruta = substr($ruta_decodificada, strlen($prefijo_api));
    }

    if ($ruta === null) {
        responder(404, false, 'La página o recurso solicitado no existe.');
    }

    if ($metodo === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    foreach ($rutas as [$metodo_ruta, $patron, $requiere_token, $accion]) {
        $expresion = '#^' . str_replace('{id}', '([0-9]+)', $patron) . '$#';

        if ($metodo !== $metodo_ruta || !preg_match($expresion, $ruta, $coincidencias)) {
            continue;
        }

        $token = leer_token($requiere_token);
        $ids = array_map('intval', array_slice($coincidencias, 1));
        [$codigo, $mensaje, $datos] = $accion($ids, $_GET, 'leer_cuerpo', $token);
        responder($codigo, true, $mensaje, $datos);
    }

    // Ninguna ruta coincidio: los 4 metodos de la API dan 404 y cualquier otro, 405.
    if (in_array($metodo, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
        responder(404, false, 'La página o recurso solicitado no existe.');
    }

    responder(405, false, 'Método no permitido.');
} catch (AppException $excepcion) {
    responder($excepcion->get_codigo_estado(), false, $excepcion->getMessage(), $excepcion->get_errores());
} catch (Throwable $excepcion) {
    error_log($excepcion->getMessage() . "\n" . $excepcion->getTraceAsString());
    responder(500, false, $mostrar_detalles_error ? $excepcion->getMessage() : 'Error interno del servidor.');
}
