<?php
namespace App\Controllers;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UploadController extends Controller
{

    public function index(Request $request, Response $response, array $args): Response
    {

        $validator = new \App\Helpers\Validator();

         $uid = $request->getAttribute('user_id');

        $rules = [
            'file' => 'required',
            'folder' => 'required'
        ];
        $messages = [

        ];

        $validationResult = $validator->make($_POST + $_FILES, $rules, $messages);

        if ($validationResult !== true) {
            return $this->respond(['errors' => $validationResult], 409);
        }

        $validData = $validator->validData;

        $uploadedFiles = $request->getUploadedFiles();

        $s3 = new \App\Helpers\S3;

        $slug = $this->db->get('users', 'slug', ['id' => $uid]);

        $uploadedFile = $uploadedFiles['file'];

        $filename = $uploadedFile->getClientFilename();

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

        $file = $nameWithoutExt.'-'.uniqid() . '.' . strtolower($ext);
        
        // Generate new key
        $key = $validData->folder.'/'. $slug . "/" . $file;

        $args = [
            'Key'         => $key,                                // Folder + filename in S3
            'Body'        => $uploadedFile->getStream(),          // Convert PHP array to JSON
            'ContentType' => $uploadedFile->getClientMediaType(), // Tell S3 it's JSON
        ];

        $result = $s3->put($args);

        if (empty($result)) {

            return $this->json(['error' => 'Unable to upload file.'], 409);
        }

        $url = $s3->getPresignedUrl($key);

        if (empty($url)) {

            return $this->json(['error' => 'Unable to retrieve url'], 409);
        }

        return $this->json(['url' => $url, 'file' => $file]);
    }

    public function remove(Request $request, Response $response, array $args): Response
    {

        $validator = new \App\Helpers\Validator();

        $data = $request->getParsedBody();

        $rules = [
            'file' => 'required',
        ];
        $messages = [

        ];

        $validationResult = $validator->make($data, $rules, $messages);

        if ($validationResult !== true) {
            return $this->respond(['errors' => $validationResult], 409);
        }

        $validData = $validator->validData;

         $uid = $request->getAttribute('user_id');

        $s3 = new \App\Helpers\S3;

        $slug = $this->db->get('users', 'slug', ['id' => $uid]);

         $key = $slug . "/" . $validData->file;

            $args = [
            'Key'         => $key,                                // Folder + filename in S3
        ];

        $result = $s3->delete($args);

        if(empty($result)){
            return $this->json(['error' => 'Unable to remove file']);
        }

        return $this->json(['message' => 'Removed file successfully']);

    }
}
