<?php

declare(strict_types=1);

namespace MaplePHP\Core\Routing;

use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use MaplePHP\Prompts\Command;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class DefaultController
{
    protected readonly ServerRequestInterface|RequestInterface $request;
    protected readonly ContainerInterface $container;
    protected Command $command;
    protected array $args;
    protected ?ConfigPropsInterface $props = null;
    protected string|bool $path;

	/**
	 * Set some data type safe object that comes from container and the dispatcher
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->args = $this->container->get("args");
        $this->request = $this->container->get("request");
    }

}
