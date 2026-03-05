<?php

namespace MaplePHP\Core\Middlewares;

use MaplePHP\Core\Exceptions\HttpException;
use MaplePHP\Http\ResponseFactory;
use MaplePHP\Prompts\Themes\Blocks;
use MaplePHP\Validate\Inp;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CliStatusError implements MiddlewareInterface
{
	private ContainerInterface $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			$response = $handler->handle($request);
			if (!Inp::value($response->getStatusCode())->isHttpSuccess()) {
				throw new HttpException(
					$response->getStatusCode(),
					$response->getReasonPhrase(),
					$request->getAttributes()
				);
			}

		} catch (HttpException $ex) {
			/** @var \MaplePHP\Prompts\Command $command */
			$command = $this->container->get("command");
			/** @var \MaplePHP\Emitron\Contracts\ConfigPropsInterface $props */
			$props = $this->container->get("props");
			$blocks = new Blocks($command);
			$blocks->addHeadline("\n--- MaplePHP Help ---");
			$blocks->addSection("Usage", "./maple [type] [options]");
			$blocks->addSection("Options", function (Blocks $inst) use ($props) {
				foreach($props->toArray() as $prop => $value) {
					$inst = $inst->addOption("--$prop", $props->getPropDesc($prop));
				}
				return $inst;
			});
			$blocks->addSpace();
			$responseFactory = new ResponseFactory();
			$response = $responseFactory->createResponse($ex->getStatusCode());
			$response = $response->withBody($command->getStream());
		}

		return $response;
	}
}
