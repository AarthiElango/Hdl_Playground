<?php
namespace App\Controllers\Run;

use App\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{

    public function schematic(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'verilog' => 'required',
            'top'     => 'required',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $count = $this->db->count('projects', ['slug' => $slug, 'user_id' => $uid]);

        if (! $count) {

            return $this->json(['error' => 'No project found'], 422);
        }

        $client = new Client();

        $username = $this->db->get('users', 'username', ['id' => $uid]);

        try {
            $response = $client->post('http://64.227.180.123:8000/run_yosys', [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json'    => $validData,
            ]);

            $body = json_decode($response->getBody(), true);

            return $this->json($body);

        } catch (RequestException $e) {
            // If the server responded with an error
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                // return $this->json([
                //     'success' => false,
                //     'error'   => $errorBody,
                //     'status'  => $e->getResponse()->getStatusCode(),
                // ], $e->getResponse()->getStatusCode());
                 return $this->json([
                    'success' => false,
                    'error'   => $errorBody,
                    'status'  => $e->getResponse()->getStatusCode(),
                ]);
            }

            // If there was no response (network error, timeout, etc.)
            return $this->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    public function simulation(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'verilog' => 'required',
            'top'     => 'required',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $count = $this->db->count('projects', ['slug' => $slug, 'user_id' => $uid]);

        if (! $count) {

            return $this->json(['error' => 'No project found'], 422);
        }

        $client = new Client();

        $username = $this->db->get('users', 'username', ['id' => $uid]);
        try{
$response = $client->post('http://64.227.180.123:8000/run_simulation', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => $validData,
        ]);

        $body = json_decode($response->getBody(), true);

        return $this->json($body);

        }
        
         catch (RequestException $e) {
            // If the server responded with an error
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody(), true);
                // return $this->json([
                //     'success' => false,
                //     'error'   => $errorBody,
                //     'status'  => $e->getResponse()->getStatusCode(),
                // ], $e->getResponse()->getStatusCode());
                 return $this->json([
                    'success' => false,
                    'error'   => $errorBody,
                    'status'  => $e->getResponse()->getStatusCode(),
                ]);
            }

            // If there was no response (network error, timeout, etc.)
            return $this->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }


    }

}
