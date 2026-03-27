<?php

declare(strict_types=1);

namespace MaplePHP\Core\Support;

use MaplePHP\Core\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;

class Twig
{
    private Environment $twig;
    private ResponseInterface $response;

    public function __construct(ContainerInterface $container, ResponseInterface $response)
    {
        $this->twig = $container->get(Environment::class);

	    $configs = App::get()->configs();
	    $configs = isset($configs['configs']) ? $configs['configs'] : ['app_title' => "MaplePHP"];

	    $this->twig->addGlobal('app', $configs);
        $this->response = $response;
    }

    public function render(string $template, array $context = []): ResponseInterface
    {
        $html = $this->twig->render($template, $context);
        $this->response->getBody()->write($html);
        return $this->response;
    }

    public function getEnvironment(): Environment
    {
        return $this->twig;
    }
}