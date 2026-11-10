<?php

declare(strict_types=1);

use App\Exceptions\AppException;
use App\Views\JsonView;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/database.php';
require __DIR__ . '/../app/Config/container.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

bootDatabase();

$container = buildContainer();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$displayErrorDetails = ($_ENV['APP_ENV'] ?? 'production') !== 'production';
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, Throwable $exception) use ($app): ResponseInterface {
        $status = $exception instanceof AppException ? $exception->getStatusCode() : 500;
        $response = $app->getResponseFactory()->createResponse();

        return JsonView::render($response, false, $exception->getMessage(), null, $status);
    }
);

$allowedOrigin = $_ENV['CORS_ALLOWED_ORIGIN'] ?? '*';

$app->add(function (ServerRequestInterface $request, $handler) use ($allowedOrigin): ResponseInterface {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response) {
    return $response;
});

(require __DIR__ . '/../app/Routes/api.php')($app);

$app->run();
