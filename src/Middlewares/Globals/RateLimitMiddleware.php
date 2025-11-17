<?php

namespace App\Middlewares\Globals;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{

    // Limits (adjust as needed)
    private $limitPerMinuteLoggedIn = 60;   // per user ID
    private $limitPerMinuteGuest    = 20;   // per session
    private $limitPerMinuteIP       = 100;  // per IP


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userId  = $_SESSION['user_id'] ?? null;  // Adjust according to your auth system
        $session = session_id();
        $ip      = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';

        // Create a key for this visitor
        if ($userId) {
            $key = "user:$userId";
            $limit = $this->limitPerMinuteLoggedIn;
        } elseif ($session) {
            $key = "session:$session";
            $limit = $this->limitPerMinuteGuest;
        } else {
            $key = "ip:$ip";
            $limit = $this->limitPerMinuteIP;
        }

        // Simple store in session (replace with Redis/db for production)
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = [];
        }

        $now = time();
        $window = 60; // 1 minute sliding window

        if (!isset($_SESSION['rate_limit'][$key])) {
            $_SESSION['rate_limit'][$key] = [
                'count' => 0,
                'start' => $now
            ];
        }

        $bucket = &$_SESSION['rate_limit'][$key];

        // Reset if time window expired
        if ($now - $bucket['start'] >= $window) {
            $bucket['count'] = 0;
            $bucket['start'] = $now;
        }

        $bucket['count']++;

        if ($bucket['count'] > $limit) {
            // Exceeded limit
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error'   => 'Too Many Requests',
                'message' => "Rate limit exceeded. Allowed $limit per minute.",
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                      ->withStatus(429);

        }

        // Continue normally
        return $handler->handle($request);
    }
}
