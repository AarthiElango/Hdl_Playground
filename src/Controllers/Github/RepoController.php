<?php
namespace App\Controllers\Github;

use App\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RepoController extends Controller
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $uid   = $request->getAttribute('uid');
        $token = $this->db->get('users', 'github_token', ['id' => $uid]);

        if (empty($token)) {
            return $this->json(['success' => false, 'error' => 'No token present'], 422);
        }

        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers'  => [
                'User-Agent'    => 'skriptx-app',
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/vnd.github+json',
            ],
        ]);

        try {
            $githubRes = $client->get('user/repos', [
                'query' => [
                    'per_page' => 100,
                    'sort'     => 'updated',
                ],
            ]);

            $repos = json_decode($githubRes->getBody()->getContents(), true);

            return $this->json(['success' => true, 'repos' => $repos]);

        } catch (RequestException $e) {

            return $this->json([
                'success' => false,
                'error'   => 'Could not fetch repositories',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        $uid   = $request->getAttribute('uid');
        $token = $this->db->get('users', 'github_token', ['id' => $uid]);
      
        if (empty($token)) {
            return $this->json(['success' => false, 'error' => 'No token present'], 422);
        }

        $validator = new \App\Helpers\Validator();
        $data      = $request->getParsedBody();
        $rules     = ['name' => 'required'];
        $messages  = [];

        $validationResult = $validator->make($data, $rules, $messages);
        if ($validationResult !== true) {
            return $this->json(['errors' => $validationResult], 422);
        }

        $validData = $validator->validData;


        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers'  => [
                'User-Agent'    => 'skriptx-app',
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/vnd.github+json',
            ],
        ]);

        try {
            $githubRes = $client->post('user/repos', [
                'json' => [
                    'name'       => $validData->name,
                    'private'    => false,
                    'auto_init'  => true
                ],
            ]);

            return $this->json(['success' => true]);

        } catch (RequestException $e) {

            return $this->json([
                'success' => false,
                'error'   => 'Could not create repository',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   public function push(Request $request, Response $response, array $args): Response
{
    $uid   = $request->getAttribute('uid');
    $token = $this->db->get('users', 'github_token', ['id' => $uid]);

    if (empty($token)) {
        return $this->json(['success' => false, 'error' => 'No GitHub token'], 422);
    }

    // Validate input
    $validator = new \App\Helpers\Validator();
    $data      = $request->getParsedBody();
    $rules     = [
        'repo'    => 'required',
        'message' => 'required',
        'files'   => 'required'
    ];
    $messages  = [];

    $validationResult = $validator->make($data, $rules, $messages);
    if ($validationResult !== true) {
        return $this->json(['errors' => $validationResult], 422);
    }

    $validData = $validator->validData;

    $repoName  = $validData->repo;
    $commitMsg = $validData->message;
    $files     = $validData->files; // associative array path => content

    $client = new Client([
        'base_uri' => 'https://api.github.com/',
        'headers' => [
            'User-Agent'    => 'skriptx-app',
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/vnd.github+json'
        ]
    ]);

    // Get GitHub username from token
    $owner = $this->getGithubUser($token);
    if (empty($owner)) {
        return $this->json(['error' => 'Repo owner not identified'], 422);
    }

    try {
        // Detect branch (main or master)
        try {
            $refRes = $client->get("repos/$owner/$repoName/git/refs/heads/main");
            $branch = "main";
        } catch (\Throwable $e) {
            $refRes = $client->get("repos/$owner/$repoName/git/refs/heads/master");
            $branch = "master";
        }

        $refData = json_decode($refRes->getBody(), true);
        $latestCommitSha = $refData['object']['sha'];

        // Get commit → tree SHA
        $commitRes = $client->get("repos/$owner/$repoName/git/commits/$latestCommitSha");
        $commitData = json_decode($commitRes->getBody(), true);
        $baseTreeSha = $commitData['tree']['sha'];

        // Create blobs
        $treeItems = [];
        foreach ($files as $filePath => $content) {

            $blobRes = $client->post("repos/$owner/$repoName/git/blobs", [
                'json' => [
                    'content'  => $content,
                    'encoding' => 'utf-8'
                ]
            ]);

            $blobData = json_decode($blobRes->getBody(), true);

            $treeItems[] = [
                'path' => $filePath,
                'mode' => '100644',
                'type' => 'blob',
                'sha'  => $blobData['sha']
            ];
        }

        // Create new tree
        $treeRes = $client->post("repos/$owner/$repoName/git/trees", [
            'json' => [
                'base_tree' => $baseTreeSha,
                'tree'      => $treeItems
            ]
        ]);
        $treeData = json_decode($treeRes->getBody(), true);
        $newTreeSha = $treeData['sha'];

        // Create commit
        $commitRes = $client->post("repos/$owner/$repoName/git/commits", [
            'json' => [
                'message' => $commitMsg,
                'tree'    => $newTreeSha,
                'parents' => [$latestCommitSha]
            ]
        ]);

        $newCommitData = json_decode($commitRes->getBody(), true);
        $newCommitSha = $newCommitData['sha'];

        // Update ref (push)
        $client->patch("repos/$owner/$repoName/git/refs/heads/$branch", [
            'json' => [
                'sha'   => $newCommitSha,
                'force' => false
            ]
        ]);

        return $this->json([
            'success' => true,
            'commit'  => $newCommitSha
        ]);

    } catch (Throwable $e) {
        return $this->json([
            'success' => false,
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function pull(Request $request, Response $response, array $args): Response
{
    $uid = $request->getAttribute('uid');
    $token = $this->db->get('users', 'github_token', ['id' => $uid]);
    $repo = $args['repo'];

    if (!$token) {
        return $this->json(['error' => 'No GitHub token'], 401);
    }

    $owner = $this->getGithubUser($token);

    $client = new \GuzzleHttp\Client([
        'base_uri' => 'https://api.github.com/',
        'headers' => [
            'User-Agent'    => 'skriptx-app',
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/vnd.github+json',
        ]
    ]);

    try {
        // Detect branch
        try {
            $branch = "main";
            $client->get("repos/$owner/$repo/git/refs/heads/main");
        } catch (\Throwable $e) {
            $branch = "master";
            $client->get("repos/$owner/$repo/git/refs/heads/master");
        }

        // Get full tree
        $treeRes = $client->get("repos/$owner/$repo/git/trees/$branch?recursive=1");
        $tree = json_decode($treeRes->getBody(), true);

        $files = [];
        foreach ($tree['tree'] as $node) {
            if ($node['type'] === 'blob') {
                $files[] = [
                    'path' => $node['path'],
                    'size' => $node['size'] ?? 0,
                    'sha'  => $node['sha']
                ];
            }
        }

        return $this->json([
            'success' => true,
            'files' => $files,
        ]);

    } catch (\Throwable $e) {
        return $this->json(['error' => $e->getMessage()], 500);
    }
}


public function pullFiles(Request $request, Response $response, array $args): Response
{
    $uid = $request->getAttribute('uid');
    $token = $this->db->get('users', 'github_token', ['id' => $uid]);

    $repo = $args['repo'];
    $paths = $request->getParsedBody()['paths'] ?? [];

    if (!$token) {
        return $this->json(['error' => 'No GitHub token'], 401);
    }

    if (empty($paths) || !is_array($paths)) {
        return $this->json(['error' => 'No file paths provided'], 422);
    }

    $owner = $this->getGithubUser($token);

    $client = new \GuzzleHttp\Client([
        'base_uri' => 'https://api.github.com/',
        'headers' => [
            'User-Agent'    => 'skriptx-app',
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/vnd.github+json',
        ]
    ]);

    $result = [];

    try {
        foreach ($paths as $path) {
            try {
                $res = $client->get("repos/$owner/$repo/contents/" . $path);
                $data = json_decode($res->getBody(), true);

                $result[$path] = [
                    'encoding' => $data['encoding'] ?? 'utf-8',
                    'content'  => base64_decode($data['content'] ?? ''),
                ];
            } catch (\Throwable $e) {
                // If one file fails, still continue others
                $result[$path] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->json([
            'success' => true,
            'files'   => $result,
        ]);

    } catch (\Throwable $e) {
        return $this->json(['error' => $e->getMessage()], 500);
    }
}



public function getGithubUser($token)
{
    $client = new Client([
        'base_uri' => 'https://api.github.com',
        'headers' => [
            'User-Agent'    => 'skriptx-app',  // required
            'Authorization' => "Bearer {$token}",
            'Accept'        => 'application/vnd.github+json',
        ]
    ]);

    $res = $client->get('/user');
    $data = json_decode($res->getBody()->getContents(), true);

    return $data['login'] ?? null;
}

}
