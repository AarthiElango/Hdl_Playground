<?php
namespace App\Middlewares\Ajax;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use function _\get;


class GoogleRecaptchaMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {

        $ENV = get($_ENV, 'APP_ENV', 'production');

        if($ENV != 'production'){
             $response = new \Slim\Psr7\Response();

            $response = $handler->handle($request);


            return $response;
        }

        if (!empty($_SESSION['userId'])) {

            $response = new \Slim\Psr7\Response();

            $response = $handler->handle($request);


            return $response;
        }

        $recaptchaToken = $request->getHeaderLine('X-Recaptcha-Token');

        if (! $recaptchaToken) {

            $payload = json_encode(['error' => 'Missing Recaptcha Token. Refresh the page']);

            $response = new \Slim\Psr7\Response();

            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(422);

        }

        $response = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret="
            . urlencode($_ENV['GOOGLE_CAPTCHA_SECRET_KEY'])
            . "&response="
            . urlencode($recaptchaToken)
            . "&remoteip="
            . $_SERVER['REMOTE_ADDR']
        );

        $result = json_decode($response, true);

        if ($result['success'] && $result['score'] >= 0.5) {
            // Passed reCAPTCHA
            // Invoke the next middleware and get response
            $response = new \Slim\Psr7\Response();

            $response = $handler->handle($request);

            // Optional: Handle the outgoing response
            // ...

            return $response;
        }

        $payload = json_encode(['error' => 'Recaptcha verification failed. Refresh the page']);

        $response = new \Slim\Psr7\Response();

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(422);
    }
}
