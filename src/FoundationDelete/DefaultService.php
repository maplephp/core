<?php

namespace MaplePHP\Core\Foundation;

use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Prompts\Command;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class DefaultService
{
    protected readonly ServerRequestInterface|RequestInterface $request;
    protected readonly ContainerInterface $container;
    protected Command $command;
    protected DispatchConfigInterface $configs;
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
        $this->configs = $this->container->get("configuration");
    }
}