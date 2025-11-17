<?php

use App\Middlewares\Ajax\CsrfMiddleware;
use App\Middlewares\Ajax\GoogleRecaptchaMiddleware;
use App\Middlewares\Ajax\GuestMiddleware;
use App\Middlewares\Ajax\UserMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/ajax/guest', function (RouteCollectorProxy $group) {

    $group->post('/login', [App\Controllers\Guest\LoginController::class, 'index'])->setName('ajax.login');
    $group->post('/register', [App\Controllers\Guest\RegisterController::class, 'index'])->setName('ajax.register');
    $group->post('/set-password', [App\Controllers\Guest\SetPasswordController::class, 'index'])->setName('ajax.set-password');
    $group->post('/forgot-password', [App\Controllers\Guest\ForgotPasswordController::class, 'index'])->setName('ajax.forgot-password');

})->add(new CsrfMiddleware)->add(new GoogleRecaptchaMiddleware)->add(new GuestMiddleware);

$app->group('/ajax/user', function (RouteCollectorProxy $group) {

    $group->post('/logout', [App\Controllers\User\LogoutController::class, 'index'])->setName('ajax.logout');

})->add(new CsrfMiddleware)->add(new UserMiddleware);


$app->group('/ajax/upload', function (RouteCollectorProxy $group) {

    $group->post('', [App\Controllers\UploadController::class, 'index'])->setName('upload');
    $group->post('/remove', [App\Controllers\UploadController::class, 'remove'])->setName('upload.remove');

})->add(new CsrfMiddleware)->add(new UserMiddleware);

$app->get('/', [App\Controllers\HomeController::class, 'index'])->setName('web.home');

$app->get('/404', [App\Controllers\ErrorController::class, 'web_not_found'])->setName('web.404');
$app->get('/500', [App\Controllers\ErrorController::class, 'web_app_error'])->setName('web.500');
