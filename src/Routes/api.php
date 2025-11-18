<?php

use App\Middlewares\Api\GuestMiddleware;
use App\Middlewares\Api\UserMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/api/guest', function (RouteCollectorProxy $group) {

    $group->post('/login', [App\Controllers\Guest\LoginController::class, 'index'])->setName('guest.login');
    $group->post('/register', [App\Controllers\Guest\RegisterController::class, 'index'])->setName('guest.register');
    $group->post('/set-password', [App\Controllers\Guest\SetPasswordController::class, 'index'])->setName('guest.set-password');
    $group->post('/forgot-password', [App\Controllers\Guest\ForgotPasswordController::class, 'index'])->setName('guest.forgot-password');

})->add(new GuestMiddleware);

$app->group('/api', function (RouteCollectorProxy $group) {

    $group->get("/projects", [App\Controllers\Projects\IndexController::class, 'index'])->setName('projects.index');
    $group->get("/projects/{slug}", [App\Controllers\Projects\IndexController::class, 'get'])->setName('projects.get');
    $group->post("/projects", [App\Controllers\Projects\SaveController::class, 'index'])->setName('projects.save');
    $group->put("/projects/{slug}", [App\Controllers\Projects\SaveController::class, 'update'])->setName('projects.update');
// $group->post("/projects/{slug}/run", [App\Controllers\Projects\RunController::class, 'index'])->setName('projects.run');
// $group->post("/projects/{slug}/logs", [App\Controllers\Projects\RunController::class, 'logs'])->setName('projects.logs');
// $group->post("/projects/{slug}/yosys/run", [App\Controllers\Projects\YosysController::class, 'run'])->setName('projects.yosys.run');
// $group->post("/projects/{slug}/yosys/simulation", [App\Controllers\Projects\YosysController::class, 'simulation'])->setName('projects.yosys.simulation');

})->add(new UserMiddleware);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->post("/projects/{slug}/run/schematic", [App\Controllers\Run\IndexController::class, 'schematic'])->setName('projects.run.yosys');
    $group->post("/projects/{slug}/run/simulation", [App\Controllers\Run\IndexController::class, 'simulation'])->setName('projects.run.simulation');

})->add(new UserMiddleware);

$app->get("/api/projects/{username}/{slug}/vcd/{uuid}", [App\Controllers\Projects\RunController::class, 'vcd'])->setName('projects.vcd');

$app->group('/api/github', function (RouteCollectorProxy $group) {

    $group->get("/auth/start", [App\Controllers\Github\AuthController::class, 'start'])->setName('github.start');
    $group->get("/auth/exists", [App\Controllers\Github\AuthController::class, 'exists'])->setName('github.exists');
    $group->get("/repos", [App\Controllers\Github\RepoController::class, 'index'])->setName('github.repos.index');
    $group->post("/repos/create", [App\Controllers\Github\RepoController::class, 'create'])->setName('github.repos.create');
    $group->post("/repos/push", [App\Controllers\Github\RepoController::class, 'push'])->setName('github.repos.push');
    $group->get('/repos/pull/{repo}', [App\Controllers\Github\RepoController::class, 'pull'])->setName('github.repos.pull');
$group->post('/repos/pull/{repo}/files', [App\Controllers\Github\RepoController::class, 'pullFiles'])->setName('github.repos.pull.files');

})->add(new UserMiddleware);

$app->get("/api/github/auth/callback", [App\Controllers\Github\AuthController::class, 'callback'])->setName('github.callback');

$app->get("/api/me", [App\Controllers\User\IndexController::class, 'me'])->setName('user.index.me');

$app->get('/migrate', [App\Database\Migration\IndexMigration::class, 'index'])->setName('migrate.index');
$app->get('/seed', [App\Database\Seeders\IndexSeeder::class, 'index'])->setName('seeder.index');

$app->get('/', [App\Controllers\HomeController::class, 'index'])->setName('web.home');

$app->get('/404', [App\Controllers\ErrorController::class, 'web_not_found'])->setName('web.404');
$app->get('/500', [App\Controllers\ErrorController::class, 'web_app_error'])->setName('web.500');
