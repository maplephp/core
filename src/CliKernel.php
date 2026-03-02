<?php
namespace MaplePHP\Core;

use Exception;
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
    protected Stream $stream;

    public function __construct(string $dir)
    {
        parent::__construct($dir);
        // Default config
        Kernel::setRouterFilePath($dir . "/routers/console.php");

        $this->stream = new Stream(Stream::STDERR);
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
        $kernel->run($request, $this->stream);
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

                $router = new RouterDispatcher($request);
                $router->setDispatchPath($request->getCliKeyword());
                //$router = new Router($request->getCliKeyword(), $request->getCliArgs());
                if (!is_file($routerFile)) {
                    throw new Exception('The routes file (' . $routerFile . ') is missing.');
                }
                require_once $routerFile;

                return $router;
            })
            ->setProp('exitCode', 0);
    }
}
