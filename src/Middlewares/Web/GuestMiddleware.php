<?php

namespace App\Middlewares\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

class GuestMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Optional: Handle the incoming request
        // ...

        if(!empty($_SESSION['userId'])){
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url = $routeParser->urlFor('web.dashboard');
            $response = $handler->handle($request);
            $uri = $request->getUri();
            return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
        }
        // Invoke the next middleware and get response
        $response = $handler->handle($request);

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}