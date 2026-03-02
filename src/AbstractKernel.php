<?php

namespace MaplePHP\Core;

use MaplePHP\Core\Configs\LoadConfigFiles;
use MaplePHP\Core\Support\Request;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Http\Path;
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

    protected array $middlewares;
    protected Env $env;
    protected ContainerInterface $container;
    private string $dir;

    public function __construct(string $dir)
    {
        if(!is_dir($dir)) {
            throw new \RuntimeException("$dir is not a directory!");
        }

        $this->dir = realpath($dir);

        $config = (new LoadConfigFiles())
            ->loadEnvFile($this->dir . "/.env")
            ->loadFile($this->dir . "/configs/configs.php");

        $this->container = new Container();
        $this->container->set("config", $config->fetch());

    }

    /**
     * Loader
     *
     * @param ServerRequestInterface $request
     * @param DispatchConfigInterface|null $config
     * @return KernelInterface
     * @throws \Exception
     */
    protected function load(ServerRequestInterface $request, ?DispatchConfigInterface $config = null): Kernel
    {
        App::boot(new Dir($this->dir), new Path([]));
        return new Kernel($this->container, $this->middlewares, $config);
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
     * Clear the default middlewares, be careful with this
     *
     * @return $this
     */
    public function unsetMiddleware(string $class): self
    {

        $inst = clone $this;
        foreach($inst->middlewares as $key => $middleware) {
            if($middleware === $class) {
                unset($inst->middlewares[$key]);
                break;
            }
        }
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


}