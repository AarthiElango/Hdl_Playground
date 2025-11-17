<?php
namespace App\Middlewares\Web;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

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
            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $url         = $routeParser->urlFor('web.login');
            $response    = $handler->handle($request);
            $uri         = $request->getUri();
            return $response
                ->withHeader('Location', $url . '?redirect=' . $uri)
                ->withStatus(302);
        }

        $dbconn = new \App\Helpers\DB;

        $uid = $_SESSION['userId'];

        if (! empty($this->args)) {

            $where = ['u.id' => $uid];

            $join = [
                '[>]roles(r)' => ['role_id' => 'id'],
            ];

            $select = 'r.name';

            $role = $dbconn->db->get('users(u)', $join, $select, $where);

            if (empty($role) || !in_array($role, $this->args)) {

                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url         = $routeParser->urlFor('web.404');
                $response    = $handler->handle($request);
                $uri         = $request->getUri();
                return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302);
            }

        }


            $count = $dbconn->db->count('users', ['blocked_at[!]'=> NULL,'id'=>$uid]);

            if($count){

                $routeParser = RouteContext::fromRequest($request)->getRouteParser();
                $url         = $routeParser->urlFor('web.404');
                $response    = $handler->handle($request);
                $uri         = $request->getUri();
                return $response
                    ->withHeader('Location', $url)
                    ->withStatus(302);

            }



        $request = $request->withAttribute('uid', $uid);

        // Invoke the next middleware and get response
        $response = $handler->handle($request);

        // Optional: Handle the outgoing response
        // ...

        return $response;
    }
}
