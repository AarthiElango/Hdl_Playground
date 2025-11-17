<?php 

namespace App\Controllers\Docs;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{

    public function privacy(Request $request, Response $response, array $args): Response
    {
       

        return $this->view($request, 'docs/privacy');
    }

     public function about(Request $request, Response $response, array $args): Response
    {
       

        return $this->view($request, 'docs/about');
    }


     public function contact(Request $request, Response $response, array $args): Response
    {
       

        return $this->view($request, 'docs/contact');
    }

    
     public function terms(Request $request, Response $response, array $args): Response
    {
       

        return $this->view($request, 'docs/terms');
    }
}
