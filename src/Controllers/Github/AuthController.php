<?php
namespace App\Controllers\Github;

use App\Controllers\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends Controller
{

    public function exists(Request $request, Response $response, array $args): Response
    {
        $uid = $request->getAttribute('uid');

        $count = $this->db->count('users', ['id' => $uid, 'github_token[!]' => null]);

        return $this->json(['success' => ! ! $count]);
    }

    public function start(Request $request, Response $response, array $args): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        // Extract Bearer token
        $token = null;
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }

        if (empty($token)) {
            return $this->json(['error' => 'Invalid token'], 422);
        }

        // validate user token (optional but recommended)
        $jwt     = new \App\Helpers\JWT;
        $decoded = $jwt->decode($token);

        if (is_string($decoded)) {
            return $this->json(['error' => 'Invalid token'], 422);
        }

        // Build GitHub OAuth URL
        $clientId    = $_ENV['GITHUB_CLIENT_ID'];
        $redirectUri = urlencode($_ENV['GITHUB_REDIRECT_URI']); // must EXACTLY match GitHub Dashboard
        $scope       = "repo";

        // VERY IMPORTANT:
        // We pass our app token through "state"
        $state = $token;

        $githubUrl = "https://github.com/login/oauth/authorize"
            . "?client_id={$clientId}"
            . "&redirect_uri={$redirectUri}"
            . "&scope={$scope}"
            . "&state={$state}";

        return $this->json(['url' => $githubUrl]);
    }

    public function callback(Request $request, Response $response, array $args): Response
    {
        $query = $request->getQueryParams();

        $code  = $query['code'] ?? null;
        $state = $query['state'] ?? null; // contains our JWT token

        if (empty($code) || empty($state)) {
            $response->getBody()->write("Missing code or state");
            return $response->withStatus(422);
        }

        // Decode user JWT
        $jwt     = new \App\Helpers\JWT;
        $decoded = $jwt->decode($state);

        if (is_string($decoded)) {
            $response->getBody()->write("Invalid state token");
            return $response->withStatus(422);
        }

        $username = $decoded->data->username ?? null;

        if (! $username) {
            $response->getBody()->write("Invalid user");
            return $response->withStatus(422);
        }

        // Exchange code for GitHub access token
        $clientId     = $_ENV['GITHUB_CLIENT_ID'];
        $clientSecret = $_ENV['GITHUB_CLIENT_SECRET'];

        $ch = curl_init("https://github.com/login/oauth/access_token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            "client_id"     => $clientId,
            "client_secret" => $clientSecret,
            "code"          => $code,
            "state"         => $state,
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $tokenResponse = json_decode($result, true);

        if (! isset($tokenResponse["access_token"])) {
            $response->getBody()->write("Failed to get GitHub token");
            return $response->withStatus(500);
        }

        $githubAccessToken = $tokenResponse["access_token"];

        // Save GitHub token to user
        $this->db->update("users", [
            "github_token" => $githubAccessToken,
        ], [
            "username" => $username,
        ]);

        // Close popup and notify frontend
        $html = "
    <script>
        window.opener.postMessage(
            { status: 'success', message: 'github_connected' },
            '*'
        );
        window.close();
    </script>
    Connecting GitHub...
    ";

        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html');
    }

}
