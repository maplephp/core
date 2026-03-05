<?php

namespace MaplePHP\Core\Render\Errors;

use MaplePHP\Core\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MaplePHP\Core\Interfaces\ErrorPageInterface;

class SimpleErrorPage implements ErrorPageInterface
{
	private ContainerInterface $container;

	public function __construct(ContainerInterface $container)
    {
		$this->container = $container;
    }

    public function render(
        ResponseInterface $response,
        ServerRequestInterface $request,
        array $context = []
    ): string {
        $config = $this->container->get("config");
        ob_start();
        require(App::get()->coreDir() . "/Render/Templates/ErrorPage.php");
        return ob_get_clean();
    }
}