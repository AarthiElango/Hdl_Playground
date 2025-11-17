<?php
namespace App\Controllers\User;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LogoutController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

      session_destroy();

       
        return $this->json(['redirect' => $_ENV['APP_URL']]);

    }

}
