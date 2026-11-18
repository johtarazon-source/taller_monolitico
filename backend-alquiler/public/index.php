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
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

bootDatabase();
Sesion::iniciar($basePath);

$container = buildContainer($basePath);
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath($basePath);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$displayErrorDetails = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($app, $container, $basePath, $displayErrorDetails): ResponseInterface {
        if (!$exception instanceof AppException
            && !$exception instanceof HttpNotFoundException
            && !$exception instanceof HttpMethodNotAllowedException) {
            error_log($exception->getMessage() . "\n" . $exception->getTraceAsString());
        }

        [$status, $mensaje] = match (true) {
            $exception instanceof AppException => [$exception->getStatusCode(), $exception->getMessage()],
            $exception instanceof HttpNotFoundException => [404, 'La página o recurso solicitado no existe.'],
            $exception instanceof HttpMethodNotAllowedException => [405, 'Método no permitido.'],
            default => [500, $displayErrorDetails ? $exception->getMessage() : 'Error interno del servidor.'],
        };

        $response = $app->getResponseFactory()->createResponse();
        $esApi = str_starts_with($request->getUri()->getPath(), $basePath . '/api');

        if ($esApi) {
            $errores = $exception instanceof AppException ? $exception->getErrores() : null;

            return JsonView::render($response, false, $mensaje, $errores, $status);
        }

        return $container->get(HtmlView::class)->render($response, 'error', [
            'titulo' => 'Error',
            'status' => $status,
            'mensaje' => $mensaje,
        ], $status);
    }
);

// La API puede consumirse desde otro origen (CORS); las paginas web no lo necesitan.
$allowedOrigins = array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGIN'] ?? ''));

$app->add(function (ServerRequestInterface $request, $handler) use ($allowedOrigins, $basePath): ResponseInterface {
    $response = $handler->handle($request);

    if (!str_starts_with($request->getUri()->getPath(), $basePath . '/api')) {
        return $response;
    }

    $origin = $request->getHeaderLine('Origin');

    if (in_array('*', $allowedOrigins, true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
    } elseif ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
        $response = $response->withHeader('Access-Control-Allow-Origin', $origin)->withHeader('Vary', 'Origin');
    }

    return $response
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->options('/api/{rutas:.+}', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response;
});

(require __DIR__ . '/../app/Routes/api.php')($app);
(require __DIR__ . '/../app/Routes/web.php')($app);

// Sin esto, la ruta OPTIONS comodin hace que una URL inexistente de la API responda 405.
$app->map(['GET', 'POST', 'PUT', 'DELETE'], '/api/{rutas:.+}', function (ServerRequestInterface $request) {
    throw new HttpNotFoundException($request);
});

$app->run();
