<?php
namespace App\Controllers\User;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

   
        return $this->view($request, 'user/dashboard/index', []);

    }

}
