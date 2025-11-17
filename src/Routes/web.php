<?php

use Slim\Routing\RouteCollectorProxy;
use App\Middlewares\Web\GuestMiddleware;
use App\Middlewares\Web\UserMiddleware;


$app->get('/', [App\Controllers\HomeController::class, 'index'])->setName('web.home');

$app->get('/404', [App\Controllers\ErrorController::class, 'web_not_found'])->setName('web.404');
$app->get('/500', [App\Controllers\ErrorController::class, 'web_app_error'])->setName('web.500');
