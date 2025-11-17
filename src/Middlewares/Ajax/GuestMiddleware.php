<?php

namespace App\Middlewares\Ajax;

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
            $payload = json_encode(['error' => 'You are not authorized to perform this action']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);
        }
        // Invoke the next middleware and get response
        $response = $handler->handle($request);

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}