<?php
namespace App\Database\Migration;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexMigration
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $path = __DIR__ . '/tables/*.php'; // folder path + pattern

        $files = glob($path);

            $dbconn = new \App\Helpers\DB;


        foreach ($files as $file) {

            $filename = pathinfo($file, PATHINFO_FILENAME);

            $columns = require __DIR__ . '/./tables/' . $filename . '.php';


            $dbconn->db->drop($filename);

            $dbconn->db->create($filename, $columns);

        }

        $html = "Migration Successful";

        $response->getBody()->write($html);

        return $response;
    }

}
