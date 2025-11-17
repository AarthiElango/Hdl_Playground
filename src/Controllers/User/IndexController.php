<?php
namespace App\Controllers\User;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{

    public function me(Request $request, Response $response, array $args): Response
    {

     

         $jwt = new \App\Helpers\JWT;

        $uid = $jwt->getUid($request);


        if(empty($uid) || is_string($uid) || !is_numeric($uid)){

            return $this->json(['success' => false, 'uid' => $uid]);
        }

        $user = (object)$this->db->get('users', '*', ['id' => $uid]);

        $args = [
            'username' => $user->username,
            'fullname' =>$user->fullname
        ];

        return $this->json(['user' => $args, 'success'=> true]);

    }

}
