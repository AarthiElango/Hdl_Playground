<?php
namespace App\Controllers;

use App\Helpers\CSRF;
use function _\get;
use Slim\Routing\RouteContext;
use Smarty\Smarty;

class Controller
{

    public $db;

    public $json_cache_path = ABSPATH . '/./json_cache';

    public $cache_time;

    public $smarty;

    public function __construct()
    {
        $dbconn           = new \App\Helpers\DB;
        $this->db         = $dbconn->db;
        $this->cache_time = $_ENV['CACHE_TIME'];

    }

    public function set_cached_json($file, $data)
    {
        $cacheFile = $this->json_cache_path . '/' . $file . '.json';

        file_put_contents($cacheFile, json_encode($data));
    }

    public function get_cached_json($file)
    {
        $data      = null;
        $cacheFile = $this->json_cache_path . '/' . $file . '.json';
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cache_time) {
            $json = file_get_contents($cacheFile);
            $data = json_decode($json, true);
        }
        return $data;
    }

    public function redirect($request, $urlname)
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();
        $url         = $routeParser->urlFor($urlname);
        $response    = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Location', $url)
            ->withStatus(302);
    }

    public function json($data = [], $status = 200)
    {

        $payload = json_encode($data);

        $response = new \Slim\Psr7\Response();

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);

    }

    public function view($request, $tpl, $data = null, $page_cache = null)
    {

        $smarty = new Smarty();
        $smarty->setTemplateDir(ABSPATH . '/src/Views/');
        $smarty->setCompileDir(ABSPATH . '/smarty/templates_c/');
        $smarty->setConfigDir(ABSPATH . '/smarty/configs/');
        $smarty->setCacheDir(ABSPATH . '/smarty/cache/');

        $ENV                   = get($_ENV, 'APP_ENV', 'production');
        $smarty->caching       = Smarty::CACHING_OFF;
        $smarty->compile_check = true;
        $smarty->force_compile = true;

        if ($ENV == 'production' && $page_cache !== false) {

            $smarty->caching        = Smarty::CACHING_LIFETIME_CURRENT;
            $smarty->compile_check  = false;
            $smarty->force_compile  = false;
            $smarty->cache_lifetime = $this->cache_time;
        }

        $response = new \Slim\Psr7\Response();

        $routeContext = RouteContext::fromRequest($request);
        $route        = $routeContext->getRoute();
        $routeName    = $route->getName();
        $smarty->assign('pageClass', '');
        if ($routeName) {

            $explode = explode('.', $routeName);
            $implode = implode(' ', $explode);
            $smarty->assign('pageClass', $implode);
        }

        $meta = [
            'title'       => get($_ENV, 'APP_TITLE', ''),
            'description' => get($_ENV, 'APP_DESCRIPTION', ''),
            'image'       => get($_ENV, 'APP_IMAGE', ''),
            'url'         => $request->getUri(),
        ];
        $smarty->assign('meta', $meta);

        if (! empty($data)) {

            foreach ($data as $key => $value) {

                $smarty->assign($key, $value);
            }

            if (! empty($data['meta'])) {
                $meta = [
                    'title'       => get($data['meta'], 'title', get($_ENV, 'APP_TITLE', '')),
                    'description' => get($data['meta'], 'description', get($_ENV, 'APP_DESCRIPTION', '')),
                    'image'       => get($data['meta'], 'image', get($_ENV, 'APP_IMAGE', '')),
                    'url'         => $request->getUri(),
                ];

                $smarty->assign('meta', $meta);

            }
        }

        $csrfContext = $_ENV['AJAX_CSRF_CONTEXT'];
        $csrf        = new CSRF($csrfContext);
        $csrf->clearHashes($csrfContext);
        $hash = $csrf->string($csrfContext);
        $smarty->assign('csrfToken', $hash);

        $smarty->assign('isUserLoggedIn', ! empty($_SESSION['userId']) ? 1 : 0);
          $ENV = get($_ENV, 'APP_ENV', 'production');
        $smarty->assign('isProduction', $ENV == 'production' ? 1 : 0);
        $smarty->assign('url', $_ENV['APP_URL']);

        $smarty->registerFilter("output", [$this, 'minify_html']);
        $smarty->registerPlugin("function", 'env', [$this, 'env']);
        $smarty->registerPlugin("function", 'svg', [$this, 'svg']);
        $smarty->registerPlugin("function", 'js', [$this, 'js']);
        $smarty->registerPlugin("function", 'css', [$this, 'css']);

        $html = $smarty->fetch($tpl . '.html', $page_cache);

        $response->getBody()->write($html);

        return $response;

    }

    public function minify_html($tpl_output, $template)
    {
        $ENV = get($_ENV, 'APP_ENV', 'production');
        if ($ENV == 'production') {
            $tpl_output = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $tpl_output);

        }
        return $tpl_output;
    }

    public function env($params = [])
    {

        if (empty($params['var'])) {

            return '';
        }

        return get($_ENV, get($params, 'var', 'DEFAULT'), '');
    }
    public function svg($params, $template)
    {
        if (! empty($params['file'])) {
            $path = ABSPATH . '/public/svgs/' . $params['file'] . '.svg';
            return file_exists($path) ? file_get_contents($path) : '';
        }
        return '';
    }

    public function js($params, $template)
    {
        $file = isset($params['file']) ? $params['file'] : '';
        if (! $file) {
            return '';
        }

        // Version number (can be static or dynamic)
        $version = isset($_ENV['APP_VERSION']) ? $_ENV['APP_VERSION'] : '1.0.0';

        // Optional: base URL
        $baseUrl = isset($_ENV['url']) ? rtrim($_ENV['url'], '/') : '';

        $src = ($baseUrl ? $baseUrl . '/' : '') . $file . '?v=' . $version;

        return '<script src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"></script>';
    }

    public function css($params, $template)
    {
        $file = isset($params['file']) ? $params['file'] : '';
        if (! $file) {
            return '';
        }

        // Version number (can be static or dynamic)
        $version = isset($_ENV['APP_VERSION']) ? $_ENV['APP_VERSION'] : '1.0.0';

        // Optional: base URL
        $baseUrl = isset($_ENV['url']) ? rtrim($_ENV['url'], '/') : '';

        $src = ($baseUrl ? $baseUrl . '/' : '') . $file . '?v=' . $version;

        return '<link href="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" rel="stylesheet" />';
    }

}
