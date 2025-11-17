<?php
namespace App\Controllers\Guest;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LoginController extends Controller
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
        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'username' => 'required',
            'password' => 'required|min:6',
            'redirectUrl' => 'nullable'
        ];
        $messages = [
            'username.required' => 'Username is required',
        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $where = [
            'OR' => [
                'email'  => $validData->username,
                'mobile' => $validData->username,
                'username' => $validData->username,
            ],
        ];

        $user = (object) $this->db->get('users', '*', $where);
        if (empty($user->id)) {

            return $this->json(['error' => 'Invalid email or mobile specified'], 422);
        }
        if (! password_verify($validData->password, $user->password)) {
            return $this->json([
                'error' => 'Invalid credentials',
            ], 422);
        }

        if(!empty($user->blocked_at)){

            return $this->json(['error' => 'Your account is restricted from administrator. Please contact administrator'], 422);

        }

      
        $jwt = new \App\Helpers\JWT;

        $data = [
            'username' => $user->username,
        ];

        $token = $jwt->encode($data);

        $user = [
            'fullname' => $user->fullname,
            'username' => $user->username
        ];

        return $this->json(['success' => true, 'token' => $token, 'user' => $user]);

    }
}
