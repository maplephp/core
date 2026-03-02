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
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->args = $this->container->get("args");
        $this->request = $this->container->get("request");
        $this->config = $this->container->get("config");
    }

}
