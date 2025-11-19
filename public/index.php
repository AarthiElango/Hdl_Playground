<?php

define('ABSPATH', __DIR__ . '/..');

session_start();

require ABSPATH . '/vendor/autoload.php';

use App\Middlewares\Globals\CorsMiddleware;
use App\Middlewares\Globals\RateLimitMiddleware;
use App\Middlewares\Globals\LoggerMiddleware;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

$dotenv = \Dotenv\Dotenv::createImmutable(ABSPATH);

$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE']);

$app = AppFactory::create();

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(new CorsMiddleware());
// $app->add(new RateLimitMiddleware());

$app->addBodyParsingMiddleware();

require ABSPATH . '/src/Routes/api.php';

$app->add(new LoggerMiddleware());

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorMiddleware->setErrorHandler(HttpNotFoundException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app) {
        $isAjax = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

        $response = $app->getResponseFactory()->createResponse();

        if ($isAjax) {
            $payload = ['error' => 'Unidentified Route'];
            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $routeParser = $app->getRouteCollector()->getRouteParser();
        $url         = $routeParser->urlFor('web.404');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302); // redirect to 404 page
    }
);

$errorMiddleware->setErrorHandler(HttpMethodNotAllowedException::class,
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app) {
        $isAjax = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

        $response = $app->getResponseFactory()->createResponse();

        if ($isAjax) {
            $payload = ['error' => 'Unidentified Route'];
            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $routeParser = $app->getRouteCollector()->getRouteParser();
        $url         = $routeParser->urlFor('web.404');

        return $response
            ->withHeader('Location', $url)
            ->withStatus(302); // redirect to 404 page
    }
);

$errorMiddleware->setDefaultErrorHandler(
    function (Request $request, Throwable $exception, bool $displayErrorDetails) use ($app) {

        $isAjax = strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';

        $response = $app->getResponseFactory()->createResponse();

        $logger = new \App\Helpers\Logger('error');
        $uri = $request->getUri();
        $errorMessage = $exception->getMessage();
        $logger->error($uri, [$errorMessage]);

        if ($isAjax) {
            $payload = ['error' => 'Application Error', 'message' => $exception->getMessage()];
            $response->getBody()->write(json_encode($payload));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
        }

        $routeParser = $app->getRouteCollector()->getRouteParser();
        $url         = $routeParser->urlFor('web.500');
       
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302); // redirect to 404 page
    }
);

$app->run();
