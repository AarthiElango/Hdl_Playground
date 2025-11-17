<?php
namespace App\Middlewares\Globals;

use App\Helpers\Logger;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class LoggerMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Optional: Handle the incoming request
        // ...

      
      $logger = new Logger('access');

        $uri = $request->getUri();

        if ($uri) {

            $context = [];

            if(!empty($_SESSION['userId'])){

                $context['user'] = $_SESSION['userId'];
            }

            $logger->access($uri, $context);

        }

        // Invoke the next middleware and get response
        $response = $handler->handle($request);

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}
