<?php

namespace MaplePHP\Core\Middlewares;

use MaplePHP\Emitron\Contracts\ConfigPropsInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CheckAllowedProps implements MiddlewareInterface
{

	protected DispatchConfigInterface $configs;
	protected array $args;
	protected ConfigPropsInterface $props;

	/**
	 * Get the active Container instance with the Dependency injector
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->args = $container->get("args");
		$this->props = $container->get("props");
	}

	/**
	 * Will automatically trigger 404 response codes if used prop does not exist
	 * as an option in config prop class, with exception for help and if CLI keyword is empty
	 *
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);
		$args = $request->getCliArgs();
		foreach ($args as $key => $value) {
			if (!$this->props->hasProp($key)) {
				return $response->withStatus(404);
			}
		}
		return $response;
	}
}
