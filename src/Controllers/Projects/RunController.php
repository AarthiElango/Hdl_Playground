<?php
namespace App\Controllers\Projects;

use App\Controllers\Controller;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RunController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'design'    => 'required',
            'testbench' => 'required',
            'uuid'      => 'required',
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

        $response = $client->post('https://esilicon.skriptx.com/hdlplayground-create.php', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => [
                'design'    => $validData->design,
                'testbench' => $validData->testbench,
                'username'  => $username,
                'uuid'      => $validData->uuid,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $this->json($body);

    }

    public function logs(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'uuid' => 'required',
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

        $response = $client->post('https://esilicon.skriptx.com/hdlplayground-logs.php', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => [
                'username' => $username,
                'uuid'     => $validData->uuid,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        return $this->json($body);

    }

    public function vcd(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $uuid = $args['uuid'];

        $username = $args['username'];

        $validator = new \App\Helpers\Validator();
        $data      = ['uuid' => $uuid, 'slug' => $slug, 'username' => $username];
        $rules     = [
            'uuid'     => 'required',
            'slug'     => 'required|exists:projects,slug',
            'username' => 'required|exists:users,username',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $uid = $this->db->get('users', 'id', ['username' => $validData->username]);

        $count = $this->db->count('projects', ['slug' => $slug, 'user_id' => $uid]);

        if (! $count) {

            return $this->json(['error' => 'No project found'], 422);
        }

        $client = new Client();

        $guzzleresponse = $client->post('https://esilicon.skriptx.com/hdlplayground-vcd-content.php', [
            'headers' => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json'    => [
                'username' => $validData->username,
                'uuid'     => $validData->uuid,
            ],
        ]);

        $vcdBody = (string) $guzzleresponse->getBody();

        $response->getBody()->write(json_encode(['vcd' => $vcdBody]));

        return $response
            ->withHeader('Content-Type', 'application/x-cdlink')
            ->withHeader('Content-Disposition', 'inline; filename="dump.vcd"');

    }
}
