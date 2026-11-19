<?php

use app\controllers\config\ControllersConfig;
use app\controllers\exceptions\AppException;
use app\controllers\exceptions\UnauthorizedException;
use app\models\config\Env;

require __DIR__ . '/../autoload.php';

Env::cargar(__DIR__ . '/../.env');
date_default_timezone_set(Env::obtener('APP_TIMEZONE', 'America/Bogota'));

$mostrar_detalles_error = Env::obtener('APP_ENV', 'production') !== 'production';
$mensaje_ruta_inexistente = 'La página o recurso solicitado no existe.';

$ruta_base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$ruta_cruda = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$es_api = str_starts_with($ruta_cruda, $ruta_base . '/api');

function responder(int $codigo_estado, bool $exito, string $mensaje, mixed $datos = null): never
{
    http_response_code($codigo_estado);
    header('Content-Type: application/json');
    echo (string) json_encode(['ok' => $exito, 'mensaje' => $mensaje, 'data' => $datos], JSON_UNESCAPED_UNICODE);
    exit;
}

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

function leer_token(bool $exigir): string
{
    $cabecera = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = str_starts_with($cabecera, 'Bearer ') ? substr($cabecera, 7) : '';

    if ($exigir && !ControllersConfig::get_sessions_controller()->validate_token($token)) {
        throw new UnauthorizedException('Token invalido o expirado.');
    }

    return $token;
}

function leer_cuerpo(): array
{
    $cuerpo_crudo = (string) file_get_contents('php://input');
    $tipo_contenido = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    $cuerpo_interpretado = null;

    if ($tipo_contenido === 'application/json' || str_ends_with($tipo_contenido, '+json')) {
        $datos = json_decode($cuerpo_crudo, true);
        $cuerpo_interpretado = is_array($datos) ? $datos : null;
    } elseif ($tipo_contenido === 'application/x-www-form-urlencoded') {
        parse_str($cuerpo_crudo, $cuerpo_interpretado);
    }

    if ($cuerpo_interpretado === null && trim($cuerpo_crudo) !== '') {
        throw new AppException('El cuerpo de la solicitud no es un JSON válido (UTF-8).', 400);
    }

    return (array) $cuerpo_interpretado;
}

$controlador_sesiones = fn () => ControllersConfig::get_sessions_controller();
$controlador_vehiculos = fn () => ControllersConfig::get_vehiculos_controller();
$controlador_clientes = fn () => ControllersConfig::get_clientes_controller();
$controlador_reservas = fn () => ControllersConfig::get_reservas_controller();
$controlador_reportes = fn () => ControllersConfig::get_reportes_controller();

$rutas = [
    ['POST', '/auth/login', false, fn ($ids_ruta, $consulta, $cuerpo) => [200, 'Inicio de sesión exitoso.', $controlador_sesiones()->login($cuerpo())]],
    ['POST', '/auth/logout', true, fn ($ids_ruta, $consulta, $cuerpo, $token) => [200, 'Sesión cerrada correctamente.', $controlador_sesiones()->logout($token)]],

    ['GET', '/resumen', true, fn () => [200, 'Resumen obtenido.', $controlador_reportes()->get_resumen()]],

    ['GET', '/vehiculos/disponibles', true, fn ($ids_ruta, $consulta) => [200, 'Vehículos disponibles obtenidos.',
        $controlador_vehiculos()->get_vehiculos_disponibles($consulta['fecha_inicio'] ?? null, $consulta['fecha_fin'] ?? null)]],
    ['GET', '/vehiculos', true, fn ($ids_ruta, $consulta) => [200, 'Vehículos obtenidos.', $controlador_vehiculos()->get_vehiculos_all($consulta)]],
    ['POST', '/vehiculos', true, fn ($ids_ruta, $consulta, $cuerpo) => [201, 'Vehículo registrado correctamente.', $controlador_vehiculos()->save_vehiculo($cuerpo())]],
    ['GET', '/vehiculos/{id}', true, fn ($ids_ruta) => [200, 'Vehículo obtenido.', $controlador_vehiculos()->get_vehiculo($ids_ruta[0])]],
    ['PUT', '/vehiculos/{id}', true, fn ($ids_ruta, $consulta, $cuerpo) => [200, 'Vehículo actualizado correctamente.', $controlador_vehiculos()->update_vehiculo($ids_ruta[0], $cuerpo())]],
    ['DELETE', '/vehiculos/{id}', true, fn ($ids_ruta) => [200, 'Vehículo eliminado correctamente.', $controlador_vehiculos()->delete_vehiculo($ids_ruta[0])]],
    ['GET', '/vehiculos/{id}/historial', true, fn ($ids_ruta) => [200, 'Historial del vehículo obtenido.', $controlador_vehiculos()->get_historial($ids_ruta[0])]],

    ['GET', '/clientes', true, fn ($ids_ruta, $consulta) => [200, 'Clientes obtenidos.', $controlador_clientes()->get_clientes_all($consulta['buscar'] ?? null)]],
    ['POST', '/clientes', true, fn ($ids_ruta, $consulta, $cuerpo) => [201, 'Cliente registrado correctamente.', $controlador_clientes()->save_cliente($cuerpo())]],
    ['GET', '/clientes/{id}', true, fn ($ids_ruta) => [200, 'Cliente obtenido.', $controlador_clientes()->get_cliente($ids_ruta[0])]],
    ['PUT', '/clientes/{id}', true, fn ($ids_ruta, $consulta, $cuerpo) => [200, 'Cliente actualizado correctamente.', $controlador_clientes()->update_cliente($ids_ruta[0], $cuerpo())]],
    ['DELETE', '/clientes/{id}', true, fn ($ids_ruta) => [200, 'Cliente eliminado correctamente.', $controlador_clientes()->delete_cliente($ids_ruta[0])]],
    ['GET', '/clientes/{id}/historial', true, fn ($ids_ruta) => [200, 'Historial del cliente obtenido.', $controlador_clientes()->get_historial($ids_ruta[0])]],

    ['GET', '/reservas', true, fn ($ids_ruta, $consulta) => [200, 'Reservas obtenidas.', $controlador_reservas()->get_reservas_all($consulta)]],
    ['POST', '/reservas', true, fn ($ids_ruta, $consulta, $cuerpo) => [201, 'Reserva registrada correctamente.', $controlador_reservas()->save_reserva($cuerpo())]],
    ['GET', '/reservas/{id}', true, fn ($ids_ruta) => [200, 'Reserva obtenida.', $controlador_reservas()->get_reserva($ids_ruta[0])]],
    ['POST', '/reservas/{id}/devolucion', true, fn ($ids_ruta, $consulta, $cuerpo) => [200, 'Devolución registrada correctamente.',
        $controlador_reservas()->return_reserva($ids_ruta[0], filter_var($cuerpo()['mantenimiento'] ?? false, FILTER_VALIDATE_BOOLEAN))]],
    ['POST', '/reservas/{id}/cancelacion', true, fn ($ids_ruta) => [200, 'Reserva cancelada correctamente.', $controlador_reservas()->cancel_reserva($ids_ruta[0])]],
];

try {
    if ($es_api) {
        aplicar_cors();
    }

    $ruta_decodificada = rawurldecode($ruta_cruda);
    $prefijo_api = $ruta_base . '/api';
    $ruta_relativa_a_api = null;

    if ($es_api && str_starts_with($ruta_decodificada, $prefijo_api . '/') && strlen($ruta_decodificada) > strlen($prefijo_api . '/')) {
        $ruta_relativa_a_api = substr($ruta_decodificada, strlen($prefijo_api));
    }

    if ($ruta_relativa_a_api === null) {
        responder(404, false, $mensaje_ruta_inexistente);
    }

    if ($metodo === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $ruta_existe_con_otro_metodo = false;

    foreach ($rutas as [$metodo_ruta, $patron_ruta, $requiere_token, $manejador]) {
        $expresion_ruta = '#^' . str_replace('{id}', '([0-9]+)', $patron_ruta) . '$#';

        if (!preg_match($expresion_ruta, $ruta_relativa_a_api, $coincidencias)) {
            continue;
        }

        if ($metodo !== $metodo_ruta) {
            $ruta_existe_con_otro_metodo = true;
            continue;
        }

        $token = leer_token($requiere_token);
        $ids_ruta = array_map('intval', array_slice($coincidencias, 1));
        [$codigo_estado, $mensaje, $datos] = $manejador($ids_ruta, $_GET, 'leer_cuerpo', $token);
        responder($codigo_estado, true, $mensaje, $datos);
    }

    if ($ruta_existe_con_otro_metodo) {
        responder(405, false, 'Método no permitido.');
    }

    responder(404, false, $mensaje_ruta_inexistente);
} catch (AppException $excepcion) {
    responder($excepcion->get_codigo_estado(), false, $excepcion->getMessage(), $excepcion->get_errores());
} catch (Throwable $excepcion) {
    error_log($excepcion->getMessage() . "\n" . $excepcion->getTraceAsString());
    responder(500, false, $mostrar_detalles_error ? $excepcion->getMessage() : 'Error interno del servidor.');
}
