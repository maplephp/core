<?php

namespace MaplePHP\Core\Routing;

use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use MaplePHP\Prompts\Command;
use MaplePHP\Validate\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class DefaultController
{
    protected readonly ServerRequestInterface|RequestInterface $request;
    protected readonly ContainerInterface $container;
    protected Command $command;
    protected array $config;
    protected array $args;
    protected ?ConfigPropsInterface $props = null;
    protected string|bool $path;

    /**
     * Set some data type safe object that comes from container and the dispatcher
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ErrorException
     */
    public function __construct(ContainerInterface $container, ResponseInterface $response)
    {
        $this->container = $container;
        $this->args = $this->container->get("args");
        $this->props = $this->container->get("props");
        $this->command = $this->container->get("command");
        $this->request = $this->container->get("request");
        $this->config = $this->container->get("config");

        print_r($this->config);
        die;

        $this->forceShowHelp($response);
    }

    /**
     * This is a temporary solution that will show help if a user
     * writes a wrong argv param in CLI
     *
     * @throws \ErrorException
     */
    protected function forceShowHelp(ResponseInterface $response): void
    {
        if (!Validator::value($response->getStatusCode())->isHttpSuccess()) {
            $props = $this->configs->getProps();
            $help = ($props->helpController !== null) ?
                $props->helpController : "\MaplePHP\Unitary\Console\Controllers\HelpController";
            $help = new $help($this->container, $response->withStatus(200));
            $help->index();
            exit(1);
        }
    }
}
