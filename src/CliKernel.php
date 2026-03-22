<?php

declare(strict_types=1);

namespace MaplePHP\Core;

use Exception;
use MaplePHP\Blunder\Handlers\CliHandler;
use MaplePHP\Core\Middlewares\CliStatusError;
use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Core\Routing\DefaultCommand;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Http\Stream;
use MaplePHP\Prompts\Command;
use MaplePHP\Prompts\Themes\Blocks;
use Psr\Http\Message\ServerRequestInterface;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\DispatchConfig;
use MaplePHP\Emitron\Kernel;
use MaplePHP\Http\Environment;
use MaplePHP\Http\ServerRequest;
use MaplePHP\Http\Uri;
use MaplePHP\Core\Middlewares\CheckAllowedProps;
use MaplePHP\Unitary\Console\Middlewares\{AddCommandMiddleware,
	CliInitMiddleware,
	ConfigPropsMiddleware,
	LocalMiddleware
};

final class CliKernel extends AbstractKernel
{
	protected array $middlewares = [
		AddCommandMiddleware::class,
		ConfigPropsMiddleware::class,
		CheckAllowedProps::class,
		LocalMiddleware::class,
		CliInitMiddleware::class,
		CliStatusError::class,
	];


	public function __construct(string $dir)
	{
		parent::__construct($dir);
		// Default config
		Kernel::setRouterFilePath($dir . "/routers/console.php");
		$this->stream = new Stream(Stream::STDERR);
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
		$cliConfig = $this->loadConfigFile('/configs/cli.php');
		return $this
			->withMiddleware($cliConfig['middleware']['global'] ?? [])
			->withErrorHandler(new CliHandler());
	}

	/**
	 * Boot the CLI app
	 *
	 * @param array $parts
	 * @return Kernel
	 * @throws \Exception
	 */
	public function boot(array $parts): KernelInterface
	{
		$env = new Environment();
		$request = new ServerRequest(new Uri($env->getUriParts($parts)), $env);
		$config = $this->dispatch($request);
		$kernel = $this->load($request, $config);

		$commandName = $request->getCliKeyword();

		if (!$commandName) {
			$this->renderHelp();
			exit(0);
		}

		$commandClass = $this->resolve($commandName);
		if (!$commandClass) {
			$command = new Command($this->stream);
			$command->error("\nUnknown command: '$commandName'\n");
			exit(1);
		}

		$kernel->run($request, $this->stream, function ($classInst, $response) {
			if ($classInst instanceof DefaultCommand) {
				$exitCode = $classInst->handle();
				if ($exitCode === 1) {
					exit(0);
				}
			}
		});
		return $kernel;
	}

	/**
	 * Access the router dispatcher
	 *
	 * @param ServerRequestInterface $request
	 * @return DispatchConfigInterface
	 * @throws \Exception
	 */
	private function dispatch(ServerRequestInterface $request): DispatchConfigInterface
	{
		$config = new DispatchConfig();
		return $config
			->setRouter(function ($routerFile) use ($request) {

				$commandName = $request->getCliKeyword();

				$router = new RouterDispatcher($request);
				$router->setDispatchCommand($commandName);

				if (!is_file(App::get()->coreDir() . "/Router/console.php")) {
					throw new \RuntimeException('The CORE routes file (' . $routerFile . ') is missing.');
				}

				if (is_file($routerFile)) {
					require_once $routerFile;
				}

				require_once App::get()->coreDir() . "/Router/console.php";
				return $router;
			})
			->setProp('exitCode', 0);
	}

	/**
	 * Get command if exits or return null to flag as unknown command
	 *
	 * @param string $name
	 * @return string|null
	 */
	private function resolve(string $name): ?string
	{
		foreach (RouterDispatcher::getCommands() as $commandClass) {
			[$commandName] = $commandClass;

			if ($commandName === $name) {
				return $commandClass[0];
			}
		}
		return null;
	}

	/**
	 * List all available commands
	 *
	 * @return void
	 */
	private function renderHelp(): void
	{
		$command = new Command($this->stream);
		$blocks = new Blocks($command);

		$blocks->addHeadline("\n--- MaplePHP Help ---", "green");
		$blocks->addSection("Usage", function (Blocks $inst) {
			return $inst
				->addExamples(
					"./maple [type] [options]",
					"You can always trigger a command and with options"
				)->addExamples(
					"./maple serve --help",
					"You can trigger a dedicated help for each command."
				);
		});

		$blocks->addSection("Available commands", function (Blocks $inst) {
			foreach ($this->getParentCommands() as $name => $desc) {
				$inst = $inst
					->addOption(
						"./maple $name",
						$desc
					);
			}
			return $inst;
		});

		$blocks->addSpace();
	}

	/**
	 * Will filter out all child command and only return the main command
	 *
	 * @return array
	 */
	protected function getParentCommands(): array
	{
		$filtered = [];
		foreach (RouterDispatcher::getCommands() as $commandClass) {
			[$commandName, $className] = $commandClass;
			$desc = is_a($className[0], DefaultCommand::class, true)
				? $className[0]::description()
				: null;

			$commandNames = explode(':', $commandName);
			$commandNameFirst = array_shift($commandNames);
			$commandNamesNext = implode(':', $commandNames);
			$filtered[$commandNameFirst][$commandNamesNext] = $desc;
		}

		return array_map(function ($item) {
			ksort($item);
			return reset($item);
		}, $filtered);
	}
}
