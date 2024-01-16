<?php

use App\Handlers\AppExceptionsHandler;
use App\Handlers\CustomErrorRenderer;
use App\Middlewares\AuthorizeApiKey;
use App\Models\Dev;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Pagination\Paginator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Slim\Psr7\Request as Psr7Request;
use Slim\Psr7\Response as Psr7Response;
use Slim\Routing\RouteCollectorProxy;

require_once __DIR__ . '/../vendor/autoload.php';

$config = include('../src/config.php');

$container = new Container();
$container->bind('settings', function () use ($config) {
    return $config;
});
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Slim routing middleware
$app->addRoutingMiddleware();

// Define Custom Error Handler
$customErrorHandler = function (
    ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app) {
    if ($logger) {
        $logger->error($exception->getMessage());
    }

    $payload = ['error' => $exception->getMessage()];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response->withHeader('Content-Type', 'application/json')
        ->withStatus($exception->getCode());
};

// Set the base path to run the app in a subdirectory.
// This path is used in urlFor().
$app->add(new BasePathMiddleware($app));


// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);

$container = $app->getContainer();

$capsule = new Manager();
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$capsule->getContainer()->singleton(
    ExceptionHandler::class,
    AppExceptionsHandler::class
);


$app->group('/api/v1/', function (RouteCollectorProxy $group) {
    $group->get('devs', function (Psr7Request $request, Psr7Response $response) {
        $page = $request->getQueryParams()['page'] ?? 1;
        $perPage = $request->getQueryParams()['per_page'] ?? 10;
        Paginator::currentPageResolver(fn () => $page);
        $devs = Dev::paginate($perPage);
        $response->getBody()->write(json_encode($devs));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
})->add(new AuthorizeApiKey());

$app->run();
