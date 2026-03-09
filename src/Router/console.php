<?php
/**
 * Shell router
 *
 * @var RouterDispatcher $router
 */

use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Core\Routing\Serve\ServeController;

$router->cli("/serve", [ServeController::class, "index"]);

