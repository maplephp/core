<?php
namespace MaplePHP\Core;

use MaplePHP\Core\Router\RouterDispatcher;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Emitron\DispatchConfig;
use MaplePHP\Http\Environment;
use MaplePHP\Http\ServerRequest;
use MaplePHP\Http\Stream;
use MaplePHP\Http\Uri;
use MaplePHP\Emitron\Kernel;
use MaplePHP\Emitron\Middlewares\{
    ContentLengthMiddleware,
    GzipMiddleware,
    HeadRequestMiddleware,
    OutputMiddleware
};
use Psr\Http\Message\ServerRequestInterface;

final class HttpKernel extends AbstractKernel
{
    private Stream $stream;

    public function __construct(string $dir)
    {
        parent::__construct($dir);
        Kernel::setRouterFilePath($dir . "/routers/web.php");

        $this->stream = new Stream(Stream::TEMP);
        // It will reverse the order
        $this->middlewares = [
            new ContentLengthMiddleware(),
            new GzipMiddleware(),
            new OutputMiddleware($this->stream),
            new HeadRequestMiddleware(),
        ];
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


    public function boot(array $parts): KernelInterface
    {
        $environment = new Environment();
        $request = new ServerRequest(new Uri($environment->getUriParts($parts)), $environment);
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
                $router->setDispatchPath($request->getUri()->getPath());
                if (!is_file($routerFile)) {
                    throw new \Exception('The routes file (' . $routerFile . ') is missing.');
                }
                require_once $routerFile;
                return $router;
            });
    }
}
