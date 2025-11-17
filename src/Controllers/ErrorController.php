<?php 


namespace App\Controllers;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ErrorController extends Controller{

    public function web_not_found(Request $request, Response $response, $args)
    {


        return $this->view($request, 'error/404');
    }

    public function web_app_error(Request $request, Response $response, $args)
    {


        return $this->view($request, 'error/500');
    }
}