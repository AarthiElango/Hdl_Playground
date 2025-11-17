<?php
namespace App\Controllers;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends Controller
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

        
        return $this->view($request, 'home/index');
    }

    public function profiles(Request $request, Response $response, array $args): Response
    {

        $start = microtime(true);

        $cachedfilename = 'home-profiles';

        $data = $this->get_cached_json($cachedfilename);

        if (! empty($data)) {

            return $this->json($data);
        }

        $helper = new \App\Helpers\Profile;
        $where  = [
            "approved_at[!]" => null,
            "ORDER"          => ["p.updated_at" => "DESC"],
            "LIMIT"          => [0, 4],
        ];

        $latest = $helper->get_profiles($request, $where);

        $where = [
            "approved_at[!]" => null,
            "ORDER"          => ["p.views" => "DESC"],
            "LIMIT"          => [0, 4],
        ];

        $popular = $helper->get_profiles($request, $where);

        $data = compact('latest', 'popular');

         $end = microtime(true);

        $executionTime = $end - $start; // in seconds
        $mins = floor($executionTime / 60);
        $secs = $executionTime % 60;

        $data['time_taken'] = "{$mins}:" . round($secs, 2);

        $this->set_cached_json($cachedfilename, $data);

        return $this->json($data);
    }
}
