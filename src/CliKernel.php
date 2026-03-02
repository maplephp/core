<?php
namespace MaplePHP\Core;

use Exception;
use MaplePHP\Emitron\Contracts\KernelInterface;
use Psr\Http\Message\ServerRequestInterface;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\DispatchConfig;
use MaplePHP\Emitron\Kernel;
use MaplePHP\Http\Environment;
use MaplePHP\Http\ServerRequest;
use MaplePHP\Http\Uri;
use MaplePHP\Core\Middlewares\CheckAllowedProps;
use MaplePHP\Unitary\Support\Router;
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
        CliInitMiddleware::class
    ];

    public function __construct(string $dir)
    {
        parent::__construct($dir);
        // Default config
        Kernel::setRouterFilePath($dir . "/routers/console.php");
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
        $config = $this->configuration($request);
        $kernel = $this->load($request, $config);
        $kernel->run($request);
        return $kernel;
    }

    /**
     * @param ServerRequestInterface $request
     * @return DispatchConfigInterface
     * @throws \Exception
     */
    private function configuration(ServerRequestInterface $request): DispatchConfigInterface
    {
        $config = new DispatchConfig();

        return $config
            ->setRouter(function ($routerFile) use ($request) {
                $router = new Router($request->getCliKeyword(), $request->getCliArgs());
                if (!is_file($routerFile)) {
                    throw new Exception('The routes file (' . $routerFile . ') is missing.');
                }
                $newRouterInst = require_once $routerFile;
                if (!($newRouterInst instanceof Router)) {
                    throw new \RuntimeException('You need to return the router instance ' .
                        'at the end of the router file (' . $routerFile . ').');
                }
                return $newRouterInst;
            })
            ->setProp('exitCode', 0);
    }
}
