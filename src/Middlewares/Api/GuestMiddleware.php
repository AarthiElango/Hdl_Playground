<?php

namespace App\Middlewares\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;


class GuestMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // Remove "Bearer " and trim spaces
        $token = null;
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (empty($token)) {

        $response = $handler->handle($request);

        return $response;
            
        }
        
       $payload = json_encode(['error' => 'Token Present. You are not authorized to perform this action']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);
    }
}