<?php
namespace App\Database\Seeders;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexSeeder
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $path = __DIR__ . '/seeds/*.php'; // folder path + pattern

        $files = glob($path);

        $dbconn = new \App\Helpers\DB;

        foreach ($files as $file) {

            $filename = pathinfo($file, PATHINFO_FILENAME);

            $values = require __DIR__ . '/./seeds/' . $filename . '.php';

            if (! empty($values)) {

                $dbconn->db->insert($filename, $values);

            }

        }

        $html = "Seeding Successful";

        $response->getBody()->write($html);

        return $response;
    }

}
