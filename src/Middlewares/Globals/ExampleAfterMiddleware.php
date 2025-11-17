<?php

namespace App\Middlewares\Globals;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExampleAfterMiddleware implements MiddlewareInterface
{


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Check some condition to determine if a new response should be created
        if (false) {

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write('New response created by middleware');
            
            return $response;
        }

        // Proceed with the next middleware
        return $handler->handle($request);
    }
}