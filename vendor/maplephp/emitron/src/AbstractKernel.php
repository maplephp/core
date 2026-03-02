<?php

/**
 * Unit — Part of the MaplePHP Unitary Kernel/Dispatcher,
 * A simple and fast dispatcher, will work great for this solution
 *
 * @package:    MaplePHP\Unitary
 * @author:     Daniel Ronkainen
 * @licence:    Apache-2.0 license, Copyright © Daniel Ronkainen
 *              Don't delete this comment, it's part of the license.
 */

declare(strict_types=1);

namespace MaplePHP\Emitron;

use MaplePHP\Container\Reflection;
use MaplePHP\Emitron\Contracts\DispatchConfigInterface;
use MaplePHP\Emitron\Contracts\EmitterInterface;
use MaplePHP\Emitron\Contracts\KernelInterface;
use MaplePHP\Emitron\Emitters\CliEmitter;
use MaplePHP\Emitron\Emitters\HttpEmitter;
use MaplePHP\Http\ResponseFactory;
use MaplePHP\Http\Stream;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractKernel implements KernelInterface
{
    public const CONFIG_FILE_PATH = __DIR__ . '/../emitron.config';
    protected static ?string $configFilePath = null;
    protected static ?string $routerFilePath = null;
    protected ContainerInterface $container;
    protected array $userMiddlewares;
    protected ?DispatchConfigInterface $dispatchConfig = null;
    protected array $config = [];

    /**
     * @param ContainerInterface $container
     * @param array $userMiddlewares
     * @param DispatchConfigInterface|null $dispatchConfig
     * @throws \Exception
     */
    public function __construct(
        ContainerInterface $container,
        array $userMiddlewares = [],
        ?DispatchConfigInterface $dispatchConfig = null,
    ) {
        $this->userMiddlewares = $userMiddlewares;
        $this->container = $container;
        $this->dispatchConfig = ($dispatchConfig === null) ?
            new DispatchConfig(static::getConfigFilePath()) : $dispatchConfig;
    }

    /**
     * Makes it easy to specify a config file inside a custom kernel file
     *
     * @param string|null $path
     * @return void
     */
    public static function setConfigFilePath(?string $path): void
    {
        static::$configFilePath = $path;
    }

    /**
     * Get expected config file
     *
     * @return string
     */
    public static function getConfigFilePath(): string
    {
        if (static::$configFilePath === null) {
            return static::CONFIG_FILE_PATH;
        }
        return static::$configFilePath;
    }

    /**
     * Set router path
     *
     * @param string|null $path
     * @return void
     */
    public static function setRouterFilePath(?string $path): void
    {
        static::$routerFilePath = $path;
    }

    /**
     * Get router path
     *
     * @return string
     */
    public static function getRouterFilePath(): string
    {
        if(static::$routerFilePath === null) {
            return realpath(dirname(self::getConfigFilePath()));
        }
        return static::$routerFilePath;
    }

    /**
     * Get config instance for configure dispatch result
     *
     * @return DispatchConfigInterface
     */
    public function getDispatchConfig(): DispatchConfigInterface
    {
        return $this->dispatchConfig;
    }

    /**
     * Will initialize the request handler with default
     * functionality that you would want.
     *
     * @param ServerRequestInterface $request
     * @param StreamInterface $stream
     * @param array $middlewares
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    protected function initRequestHandler(
        ServerRequestInterface $request,
        StreamInterface $stream,
        array $middlewares = []
    ) : ResponseInterface {
        $factory = new ResponseFactory($stream);
        $this->bindInterfaces([
            "ContainerInterface" => $this->container, "RequestInterface" => $request,
            "ServerRequestInterface" => $request, "StreamInterface" => $stream,
        ]);
        $middlewares = array_merge($this->userMiddlewares, $middlewares);
        $handler = new RequestHandler($middlewares, $factory);
        $response = $handler->handle($request);
        $this->bindInterfaces(["ResponseInterface" => $response]);
        return $response;
    }

    /**
     * Bind instances (singletons) to interface classes so they can be resolved
     * through the dependency injector.
     *
     * @param array<string, object> $bindings
     * @return void
     */
    protected function bindInterfaces(array $bindings): void
    {
        Reflection::interfaceFactory(function (string $className) use ($bindings) {
            return $bindings[$className] ?? null;
        });
    }

    /**
     * Get the expected body (stream)
     *
     * @param StreamInterface|null $stream
     * @return StreamInterface
     */
    protected function getBody(?StreamInterface $stream): StreamInterface
    {
        if($stream === null) {
            return new Stream($this->isCli() ? Stream::STDOUT : Stream::TEMP);
        }
        return $stream;
    }

    /**
     * Check if is inside a command line interface (CLI)
     *
     * @return bool
     */
    protected function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Get emitter based on a platform
     *
     * @return EmitterInterface
     */
    protected function createEmitter(): EmitterInterface
    {
        return $this->isCli() ? new CliEmitter() : new HttpEmitter();
    }
}