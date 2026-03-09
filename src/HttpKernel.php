<?php

declare(strict_types=1);

namespace MaplePHP\Core;

use MaplePHP\Blunder\Handlers\HtmlHandler;
use MaplePHP\Core\Interfaces\KernelLoadInterface;
use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Emitron\DispatchConfig;
use MaplePHP\Http\Environment;
use MaplePHP\Http\ServerRequest;
use MaplePHP\Http\Stream;
use MaplePHP\Http\Uri;
use MaplePHP\Emitron\Kernel;
use Psr\Http\Message\ServerRequestInterface;

final class HttpKernel extends AbstractKernel implements KernelLoadInterface
{

	public function __construct(string $dir)
	{
		parent::__construct($dir);
		Kernel::setRouterFilePath($dir . "/routers/web.php");
		$this->stream = new Stream(Stream::TEMP);
	}

	/**
	 * Initialize the HTTP kernel with default framework configuration.
	 *
	 * This method loads HTTP-related configuration from `/configs/http.php`
	 * and registers globally configured middleware. It also attaches the
	 * default error handler used for rendering exceptions and runtime errors.
	 *
	 * This method is intended to be called before booting the kernel.
	 *
	 * @return self
	 */
	public function init(): self
	{
		$httpConfig = $this->loadConfigFile('/configs/http.php');
		return $this
			->withMiddleware($httpConfig['middleware']['global'] ?? [])
			->withErrorHandler(new HtmlHandler());
	}

	/**
	 * Boot the application and execute the request lifecycle.
	 *
	 * This method prepares the runtime environment, constructs the PSR-7
	 * request object, loads the application configuration, builds the
	 * kernel pipeline, and finally executes the request.
	 *
	 * @param array $parts Additional URI parts used to construct the request
	 *                     (typically provided by the front controller).
	 *
	 * @return KernelInterface The running kernel instance.
	 * @throws \Exception
	 */
	public function boot(array $parts): KernelInterface
	{
		$environment = new Environment();
		$request = new ServerRequest(new Uri($environment->getUriParts($parts)), $environment);
		$config = $this->dispatch($request);
		$kernel = $this->load($request, $config);
		$kernel->run($request, $this->stream);
		return $kernel;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @return DispatchConfigInterface
	 * @throws \Exception
	 */
	private function dispatch(ServerRequestInterface $request): DispatchConfigInterface
	{
		$config = new DispatchConfig();
		return $config
			->setRouter(function ($routerFile) use ($request) {
				$router = new RouterDispatcher($request);
				$router->setDispatchPath($request->getUri()->getPath());
				if (!is_file($routerFile)) {
					throw new \Exception('The routes file (' . $routerFile . ') is missing.');
				}
				require_once $routerFile;
				require_once App::get()->coreDir() . "/Router/web.php";
				return $router;
			});
	}
}
