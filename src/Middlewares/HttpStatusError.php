<?php

namespace MaplePHP\Core\Middlewares;

use MaplePHP\Container\Autowire;
use MaplePHP\Core\Exceptions\HttpException;
use MaplePHP\Core\Interfaces\ErrorPageInterface;
use MaplePHP\Core\Render\Errors\SimpleErrorPage;
use MaplePHP\Http\ResponseFactory;
use MaplePHP\Http\StreamFactory;
use MaplePHP\Validate\Inp;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpStatusError implements MiddlewareInterface
{

	private ContainerInterface $container;
	/**
	 * @var ErrorPageInterface|mixed|null
	 */
	private ErrorPageInterface $errorPage;

	/**
	 * @throws \ReflectionException
	 */
	public function __construct(?ErrorPageInterface $errorPage = null)
	{
		if ($errorPage === null) {
			$wire = new Autowire(SimpleErrorPage::class);
			$errorPage = $wire->run();
		}
		$this->errorPage = $errorPage;
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
			$streamFactory = new StreamFactory();
			$responseFactory = new ResponseFactory();
			$phrase = ($ex->getReasonPhrase() === "") ? null : $ex->getReasonPhrase();
			$response = $responseFactory->createResponse($ex->getStatusCode());
			$output = $this->errorPage->render($response, $request, ['message' => $phrase]);
			$response = $response->withBody($streamFactory->createStream($output));
		}
		return $response;
	}
}
