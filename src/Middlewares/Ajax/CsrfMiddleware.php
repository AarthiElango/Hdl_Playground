<?php

namespace App\Middlewares\Ajax;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;
use App\Helpers\CSRF;


class CsrfMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Optional: Handle the incoming request
        // ...

      

        $hash = $request->getHeader('X-Csrf-Token');

        $csrfContext= $_ENV['AJAX_CSRF_CONTEXT'];

        $csrf = new CSRF($csrfContext);

        $hashes=$csrf->getHashes($csrfContext, -1);
        

        $validate = !empty($hash)?$csrf->validate($csrfContext,trim($hash[0])):false;

        if(!$validate){
            
            $payload = json_encode(['error' => 'Page timeout. Please refresh the page']);

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