<?php
/**
 * Shell router
 *
 * @var RouterDispatcher $router
 */

use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Core\Routing\Make\MakeController;
use MaplePHP\Core\Routing\Serve\ServeController;
use MaplePHP\Core\Routing\Migrations\MigrateController;

$router->cli("serve", [ServeController::class, "index"]);
$router->cli("make", [MakeController::class, "index"]);
$router->cli("migrate", [MigrateController::class, "index"]);
$router->cli("migrate:up", [MigrateController::class, "up"]);
$router->cli("migrate:down", [MigrateController::class, "down"]);
$router->cli("migrate:fresh", [MigrateController::class, "fresh"]);
$router->cli("migrate:clear", [MigrateController::class, "clear"]);

