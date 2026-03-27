<?php

declare(strict_types=1);

namespace MaplePHP\Core;

use MaplePHP\Core\Configs\LoadConfigFiles;
use MaplePHP\Core\Support\ServiceProvider;
use MaplePHP\DTO\Format\Clock;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Http\Stream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use MaplePHP\Blunder\Interfaces\AbstractHandlerInterface;
use MaplePHP\Blunder\Run;
use MaplePHP\Container\Container;
use MaplePHP\Core\Support\Dir;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\Kernel;
use MaplePHP\Http\Env;

abstract class AbstractKernel
{

	protected Stream $stream;
	protected array $middlewares = [];
	protected Env $env;
	protected ContainerInterface $container;
	protected string $dir;
	protected array $config;

	public function __construct(string $dir)
	{
		if (!is_dir($dir)) {
			throw new \RuntimeException("$dir is not a directory!");
		}

		$this->dir = realpath($dir);
		$config = (new LoadConfigFiles())
			->add("dir", $this->dir)
			->loadEnvFile($this->dir . "/.env")
			->loadFiles($this->dir . "/configs/");

		$this->config = $config->fetch();

		$app = App::boot(new Dir($this->dir), $this->config);
		$this->container = new Container();
		$this->container->set("config", $this->config);
		$this->container->set("app", $app);

		Clock::setDefaultLocale($this->config['configs']['locale']);
		Clock::setDefaultTimezone($this->config['configs']['timezone']);
	}

	/**
	 * Loader
	 *
	 * @param ServerRequestInterface $request
	 * @param DispatchConfigInterface|null $config
	 * @return KernelInterface
	 * @throws \Exception
	 */
	protected function load(ServerRequestInterface $request, ?DispatchConfigInterface $config = null): KernelInterface
	{
		$this->bootServiceProviders();
		return new Kernel($this->container, $this->middlewares, $config);
	}

	/**
	 * Boot service providers
	 *
	 * @return void
	 */
	protected function bootServiceProviders()
	{
		if (isset($this->config['providers'])) {
			$providers = [];

			// We want to register first, that way the providers could talk to eachother
			// through the container or event listners if you want.
			foreach ($this->config['providers'] as $providerClass) {
				$provider = new $providerClass();
				if (!($provider instanceof ServiceProvider)) {
					throw new \RuntimeException(
						"$providerClass is not an instance of " . ServiceProvider::class . "!"
					);
				}
				$provider->register($this->container);
				$providers[] = $provider;
			}

			foreach ($providers as $provider) {
				$provider->boot();
			}
		}
	}

	/**
	 * @param Stream $stream
	 * @return $this
	 */
	public function withStream(Stream $stream): self
	{
		$inst = clone $this;
		$inst->stream = $stream;
		return $inst;
	}

	/**
	 * Clear the default middlewares, be careful with this
	 *
	 * @return $this
	 */
	public function clearDefaultMiddleware(): self
	{
		$inst = clone $this;
		$inst->middlewares = [];
		return $inst;
	}

	/**
	 * Add custom middlewares, follow PSR convention
	 *
	 * @param array $middleware
	 * @return $this
	 */
	public function withMiddleware(array $middleware): self
	{
		$inst = clone $this;
		$inst->middlewares = array_merge($inst->middlewares, $middleware);
		return $inst;
	}

	/**
	 * Change router file
	 *
	 * @param string $path
	 * @return $this
	 */
	public function withRouter(string $path): self
	{
		$inst = clone $this;
		Kernel::setRouterFilePath($path);
		return $inst;
	}

	/**
	 * Change the config file
	 *
	 * @param string $path
	 * @return $this
	 */
	public function withConfig(string $path): self
	{
		$inst = clone $this;
		Kernel::setConfigFilePath($path);
		return $inst;
	}

	/**
	 * Default error handler boot
	 * @param AbstractHandlerInterface $handler
	 * @return $this
	 */
	public function withErrorHandler(AbstractHandlerInterface $handler): self
	{
		$inst = clone $this;
		$run = new Run($handler);
		$run->severity()
			->excludeSeverityLevels([E_USER_WARNING, E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED])
			->redirectTo(function () {
				// Let PHP’s default error handler process excluded severities
				return false;
			});
		$run->setExitCode(1);
		$run->load();
		return $inst;
	}

	/**
	 * Helper method to load config file and return array
	 *
	 * @param string $relativeFilePath
	 * @return array
	 */
	protected function loadConfigFile(string $relativeFilePath): array
	{
		$file = "/" . ltrim($relativeFilePath, "/");
		if (!is_file($this->dir . $file)) {
			return [];
		}
		$config = require $this->dir . $file;
		return is_array($config) ? $config : [];
	}
}