<?php 

namespace App\Controllers\Guest;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{
    /**
     * Handle guest login request.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */

    public function index(Request $request, Response $response, array $args): Response
    {
       

      

        return $this->view($request, 'guest/index');
    }
}
