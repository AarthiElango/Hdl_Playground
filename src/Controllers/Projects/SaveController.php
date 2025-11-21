<?php
namespace App\Controllers\Projects;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaveController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'title'       => 'required|min:3|max:200',
            'description' => 'nullable|min:3|max:500',
            'template_id'    => 'required|numeric',
            'tool_id'    => 'required|numeric',
            'files'    => 'required|array',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $uid = $request->getAttribute('uid');

        $unique = false;

        $random = new \App\Helpers\Random;

        $count = 1;

        $length = 6;

        do {

            $slug = strtolower($random->string($length));

            $count = $this->db->count('projects', ['slug' => $slug]);

            if (! $count) {

                $unique = true;
            }

            if ($count >= 100) {
                $count = 1;
                $length++;
            }

        } while (! $unique);

        $args = [
            'slug'        => $slug,
            'title'       => $validData->title,
            'description' => $validData->description,
            'template_id' => $validData->template_id,
            'tool_id' => $validData->tool_id,
            'user_id'     => $uid,
            'files'    => serialize($validData->files),
        ];

        $this->db->insert('projects', $args);

        return $this->json(['success' => true, 'slug' => $slug]);

    }

    public function update(Request $request, Response $response, array $args): Response
    {

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = [
            'files'    => 'nullable|array',
            'title'       => 'nullable|string',
            'description' => 'nullable|string',
            'template_id' => 'nullable|numeric',
            'tool_id' => 'nullable|numeric',
        ];
        $messages = [

        ];
        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }
        $validData = $validator->validData;

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $where = [
            'user_id' => $uid,
            'slug'    => $slug,
        ];

        $id = $this->db->get('projects', 'id', $where);

        if (empty($id)) {

            return $this->json(['error' => 'Invalid project'], 422);
        }

        $args = [];

        if(!empty($validData->files)){

            $args['files'] = serialize($validData->files);
        }

          if(!empty($validData->title)){

            $args['title'] = $validData->title;
        }

          if(!empty($validData->description)){

            $args['description'] = $validData->description;
        }

           if(!empty($validData->template_id)){

            $args['template_id'] = $validData->template_id;
        }

           if(!empty($validData->tool_id)){

            $args['tool_id'] = $validData->tool_id;
        }

        $this->db->update('projects', $args, ['id' => $id]);

        return $this->json(['success' => true]);

    }

}
