<?php

declare(strict_types=1);

use App\Exceptions\AppException;
use App\Support\Sesion;
use App\Views\HtmlView;
use App\Views\JsonView;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';
require __DIR__ . '/../app/Config/container.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Bogota');

// Permite servir la aplicacion desde una subcarpeta de Apache (XAMPP) o con `php -S`.
$rutaBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

iniciarBaseDatos();
Sesion::iniciar($rutaBase);

$contenedor = construirContenedor($rutaBase);
AppFactory::setContainer($contenedor);
$aplicacion = AppFactory::create();
$aplicacion->setBasePath($rutaBase);

$aplicacion->addBodyParsingMiddleware();
$aplicacion->addRoutingMiddleware();

$mostrarDetallesError = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
$middlewareErrores = $aplicacion->addErrorMiddleware($mostrarDetallesError, true, true);
$middlewareErrores->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($aplicacion, $contenedor, $rutaBase, $mostrarDetallesError): ResponseInterface {
        if (!$exception instanceof AppException
            && !$exception instanceof HttpNotFoundException
            && !$exception instanceof HttpMethodNotAllowedException) {
            error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }

        if ($exception instanceof AppException) {
            $codigoEstado = $exception->obtenerCodigoEstado();
            $mensaje = $exception->getMessage();
        } elseif ($exception instanceof HttpNotFoundException) {
            $codigoEstado = 404;
            $mensaje = 'La página o recurso solicitado no existe.';
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $codigoEstado = 405;
            $mensaje = 'Método no permitido.';
        } else {
            $codigoEstado = 500;
            $mensaje = $mostrarDetallesError ? $exception->getMessage() : 'Error interno del servidor.';
        }

        $response = $aplicacion->getResponseFactory()->createResponse();
        $esApi = str_starts_with($request->getUri()->getPath(), $rutaBase . '/api');

        if ($esApi) {
            $errores = $exception instanceof AppException ? $exception->obtenerErrores() : null;

            return JsonView::render($response, false, $mensaje, $errores, $codigoEstado);
        }

        return $contenedor->get(HtmlView::class)->render($response, 'error', [
            'titulo' => 'Error',
            'codigoEstado' => $codigoEstado,
            'mensaje' => $mensaje,
        ], $codigoEstado);
    }
);

// La API puede consumirse desde otro origen (CORS); las paginas web no lo necesitan.
$origenesPermitidos = array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGIN'] ?? ''));

$aplicacion->add(function (ServerRequestInterface $request, $handler) use ($origenesPermitidos, $rutaBase): ResponseInterface {
    $response = $handler->handle($request);

    if (!str_starts_with($request->getUri()->getPath(), $rutaBase . '/api')) {
        return $response;
    }

    $origenSolicitud = $request->getHeaderLine('Origin');

    if (in_array('*', $origenesPermitidos, true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    } elseif ($origenSolicitud !== '' && in_array($origenSolicitud, $origenesPermitidos, true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origenSolicitud)->withHeader('Vary', 'Origin');
    }

    return $response
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$aplicacion->options('/api/{rutas:.+}', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response;
});

(require __DIR__ . '/../app/Routes/api.php')($aplicacion);
(require __DIR__ . '/../app/Routes/web.php')($aplicacion);

// Sin esto, la ruta OPTIONS comodin hace que una URL inexistente de la API responda 405.
$aplicacion->map(['GET', 'POST', 'PUT', 'DELETE'], '/api/{rutas:.+}', function (ServerRequestInterface $request) {
    throw new HttpNotFoundException($request);
});

$aplicacion->run();
