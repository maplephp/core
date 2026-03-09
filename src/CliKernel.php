<?php

declare(strict_types=1);

namespace MaplePHP\Core;

use Exception;
use MaplePHP\Blunder\Handlers\CliHandler;
use MaplePHP\Core\Middlewares\CliStatusError;
use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Http\Stream;
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
    LocalMiddleware};

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
                $router->setDispatchPath($request->getCliKeyword());
                //$router = new Router($request->getCliKeyword(), $request->getCliArgs());
                if (!is_file($routerFile)) {
                    throw new Exception('The routes file (' . $routerFile . ') is missing.');
                }
                require_once $routerFile;
	            require_once App::get()->coreDir() . "/Router/console.php";
                return $router;
            })
            ->setProp('exitCode', 0);
    }
}
