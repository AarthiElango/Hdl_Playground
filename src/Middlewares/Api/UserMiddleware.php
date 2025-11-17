<?php

namespace App\Middlewares\Api;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;


class UserMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
       
        
        // Invoke the next middleware and get response
        $jwt = new \App\Helpers\JWT;

        $uid = $jwt->getUid($request);


        if(empty($uid) || is_string($uid) || !is_numeric($uid)){

            $payload = json_encode(['error' => 'You are not authorized to perform this action', 'jwt' => $uid]);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);
        }

       $request = $request->withAttribute('uid', $uid);

        $response = $handler->handle($request);

        return $response;
    }
}