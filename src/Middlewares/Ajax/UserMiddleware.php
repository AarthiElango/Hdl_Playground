<?php
namespace App\Middlewares\Ajax;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class UserMiddleware implements MiddlewareInterface
{
     private $args;

    public function __construct($args = null)
    {
        $this->args = $args;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Optional: Handle the incoming request
        // ...

        if (empty($_SESSION['userId'])) {
            $payload = json_encode(['error' => 'You are not authorized to perform this action']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);
        }

         $uid = $_SESSION['userId'];

            $dbconn = new \App\Helpers\DB;


          if (! empty($this->args)) {


            $where = ['u.id' => $uid];

            $join = [
                '[>]roles(r)' => ['role_id' => 'id'],
            ];

            $select = 'r.name';

            $role = $dbconn->db->get('users(u)', $join, $select, $where);

            if (empty($role) || !in_array($role, $this->args)) {

              $payload = json_encode(['error' => 'You are not authorized to perform this action']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);
            }

        }
        
                      $count = $dbconn->db->count('users', ['blocked_at[!]'=> NULL,'id'=>$uid]);


            if($count){

               $payload = json_encode(['error' => 'You are not authorized to perform this action']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);
    
            return $response
                      ->withHeader('Content-Type', 'application/json')
                      ->withStatus(422);

            }


        $request = $request->withAttribute('uid', $uid);

        // Invoke the next middleware and get response
        $response = $handler->handle($request);

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}
