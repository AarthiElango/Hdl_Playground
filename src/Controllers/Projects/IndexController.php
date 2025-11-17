<?php
namespace App\Controllers\Projects;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $projects = $this->db->select('projects',['slug', 'title','description', 'template_id', 'updated_at'], ['user_id' => $uid, 'ORDER'=>['updated_at'=>'DESC']]);
  
        return $this->json(['projects'=>$projects]);

    }

    public function get(Request $request, Response $response, array $args): Response
    {

        $uid = $request->getAttribute('uid');

        $slug = $args['slug'];

        $where = [
            'user_id' => $uid,
            'slug'  => $slug
        ];
     
        $project = $this->db->get('projects', ['title', 'files', 'description', 'slug', 'template_id', 'tool_id'], $where);

        if(empty($project)){

            return $this->json(['success'=> true, 'project'=> NULL]);
        }

        $project['files'] = unserialize($project['files']);

        return $this->json(['success'=> true, 'project'=> $project]);

    }
}
