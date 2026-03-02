<?php
namespace MaplePHP\Core;

use MaplePHP\Container\Container;
use MaplePHP\Emitron\Contracts\KernelInterface;
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

    /**
     * @param array $parts
     * @return KernelInterface
     * @throws \Exception
     */
    public function boot(array $parts): KernelInterface
    {
        $environment = new Environment();
        $container = new Container();
        $container->set("env", $this->env);
        $request = new ServerRequest(new Uri($environment->getUriParts($parts)), $environment);

        $kernel = $this->load($request);
        //$kernel = new Kernel($container, $this->middlewares);
        $kernel->run($request, $this->stream);
        return $kernel;
    }
}
